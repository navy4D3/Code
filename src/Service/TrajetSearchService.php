<?php
namespace App\Service;

use App\Controller\TrajetController;
use App\Document\GoogleData;
use App\Entity\User;
use App\Enum\Statut;
use App\Repository\TrajetRepository;
use Doctrine\ODM\MongoDB\DocumentManager;

class TrajetSearchService
{
    public function __construct(private TrajetRepository $trajetRepository, private TrajetController $trajetController, private DocumentManager $dm) {}

    public function saveGoogleData(int $trajetId, array $googleResponse): void
    {
        $googleData = new GoogleData();
        $googleData->setTrajetId((string)$trajetId);
        $googleData->setData($googleResponse);

        $this->dm->persist($googleData);
        $this->dm->flush();
    }

    public function getGoogleData(int $trajetId): ?array
    {
        $document = $this->dm->getRepository(GoogleData::class)->findOneBy(['trajetId' => (string)$trajetId]);

        return $document ? $document->getData() : null;
    }

    public function getGpsPoints($googleDataId): array
    {
        // $monGoDbDataId = $this->getGoogleDataId(); // JSON brut de Google
        // // $data = json_decode($json, true);

        // ajuster pour recuperer le json depuis MonGoDB
        $googleDataDocument = $this->dm->getRepository(GoogleData::class)->findOneBy(['trajetId' => $googleDataId]);
        $data = $googleDataDocument->getData();

        $polyline = $data['overview_polyline']['points'];
        // $polyline = $data['summary'];

        return $this->decodePolyline($polyline);
    }

    public function decodePolyline(string $polyline): array
    {
        if (!$polyline) {
            return [];
        }

        $points = [];
    
        $index = 0;
        $lat = 0;
        $lng = 0;
        $length = strlen($polyline);
    
        while ($index < $length) {
            $result = 1;
            $shift = 0;
            do {
                $b = ord($polyline[$index++]) - 63 - 1;
                $result += $b << $shift;
                $shift += 5;
            } while ($b >= 0x1f);
            $lat += ($result & 1) ? ~($result >> 1) : ($result >> 1);
    
            $result = 1;
            $shift = 0;
            do {
                $b = ord($polyline[$index++]) - 63 - 1;
                $result += $b << $shift;
                $shift += 5;
            } while ($b >= 0x1f);
            $lng += ($result & 1) ? ~($result >> 1) : ($result >> 1);
    
            $points[] = [
                'lat' => $lat * 1e-5,
                'lng' => $lng * 1e-5,
            ];
        }
    
        return $points;
    }

    public function findMatchingTrips(array $startCoords, array $endCoords, $date, $nbPlace, string $currentUserId = ""): array
    {
        $isOnDate = true;
        $trips = $this->trajetRepository->findByDateStatutAndPlaces($date, $nbPlace, $isOnDate, $currentUserId);
        if (empty($trips)) {
            $isOnDate = false;
            $trips = $this->trajetRepository->findByDateStatutAndPlaces($date, $nbPlace, $isOnDate, $currentUserId);

        }
        
        $matchingTrips = [];

        foreach ($trips as $trip) {
            $points = $this->getGpsPoints($trip->getId()); // À implémenter : decode JSON et extraire les lat/lng

            $tripIsPlan = $trip->getStatut() == Statut::Planifie;
            if (
                $this->isCloseToRoute($startCoords, $points) &&
                $this->isCloseToRoute($endCoords, $points) &&
                $this->isSameDirection($startCoords, $endCoords, $points) &&
                $tripIsPlan
            ) {
                $nearestStartPoint = $this->findNearestPoint($startCoords, $points);
                $nearestEndPoint = $this->findNearestPoint($endCoords, $points);
                $placesRestante = $trip->getVoiture()->getPlaces() - count($trip->getParticipants());
                $matchingTrips[] = [
                    'datas' => $trip,
                    'timeDatas' => $this->trajetController->showTrajetDateAndTime($trip),
                    'placesRestante' => $placesRestante,
                    'nearestStartPoint' => $nearestStartPoint, 
                    'nearestEndPoint' => $nearestEndPoint
                ];
            }
        }

        return ['trajets' => $matchingTrips, 'isOnDate' => $isOnDate];
    }

    private function isCloseToRoute(array $coord, array $routePoints, float $thresholdKilometers = 50): bool
    {
        foreach ($routePoints as $point) {
            if ($this->haversineDistance($coord, $point) < $thresholdKilometers) {
                return true;
            }
        }
        return false;
    }

    private function haversineDistance(array $point1, array $point2): float
    {
        $earthRadius = 6371; // m
        $lat1 = deg2rad($point1['lat']);
        $lon1 = deg2rad($point1['lng']);
        $lat2 = deg2rad($point2['lat']);
        $lon2 = deg2rad($point2['lng']);

        $deltaLat = $lat2 - $lat1;
        $deltaLon = $lon2 - $lon1;

        $a = sin($deltaLat / 2) ** 2 +
             cos($lat1) * cos($lat2) * sin($deltaLon / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    private function isSameDirection(array $userStart, array $userEnd, array $trajetPoints): bool
    {
        $nearestStartIndex = $this->findNearestPoint($userStart, $trajetPoints)['index'];
        $nearestEndIndex = $this->findNearestPoint($userEnd, $trajetPoints)['index'];

        return $nearestStartIndex !== null && $nearestEndIndex !== null && $nearestStartIndex < $nearestEndIndex;
    }

    private function findNearestPoint(array $target, array $points): ?array
    {
        $minDistance = PHP_INT_MAX;
        $closestIndex = null;

        foreach ($points as $index => $point) {
            $distance = $this->haversineDistance($target, $point);
            if ($distance < $minDistance) {
                $minDistance = $distance;
                $closestIndex = $index;
            }
        }

        // Optionnel : ne considérer que les points à moins de 10 km
        $minDistance < 50 ? $closestIndex : null;
        return ['index' => $closestIndex, 'distance' => round($minDistance, 1)];
    }
}
