<?php

namespace App\Controller;

use App\Entity\Avis;
use App\Entity\Reservation;
use App\Entity\Trajet;
use App\Entity\User;
use App\Enum\StatutAvis;
use App\Enum\StatutReservation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ReservationController extends AbstractController{
    #[Route('/ajouter-passagers', name: 'app_ajouter_passagers')]
    public function ajouterPassagers(Request $request, EntityManagerInterface $em):Response
    {
        $userId = $request->get('user');
        $trajetId = $request->get('trajet');
        $places = $request->get('places');
        $user = $em->getRepository(User::class)->find($userId);
        $trajet = $em->getRepository(Trajet::class)->find($trajetId);

        if ($user && $trajet && $places) {
            $reservation = new Reservation();

            $reservation->setUser($user);
            $reservation->setTrajet($trajet);
            $reservation->setNbPlaces($places);
            $reservation->setStatut(StatutReservation::Enregistre);

            $em->persist($reservation);
            $em->flush();

            return $this->redirectToRoute('app_user_profil');
        }

        return new Response("Erreur lors de l'ajout du passager");
        
    }

    #[Route('/cancel-reservation/{id}', name: 'app_cancel_reservation')]
    public function cancelReservation(EntityManagerInterface $em, $id): Response
    {
        $reservationToDelete = $em->getRepository(Reservation::class)->find($id);

        $em->remove($reservationToDelete);

        $em->flush();

        return $this->redirectToRoute('app_user_profil');
    }

    #[Route('/traiter-reservation/{id}', name: 'app_traiter_reservation')]
    public function treatReservation(Request $request, EntityManagerInterface $em, $id): Response
    {
        $treatmentType = $request->query->get('type');
        $isAvisVisible =$request->query->get('avis_visible');
        $avisId = $request->query->get('avis');

        $reservation = $em->getRepository(Reservation::class)->find($id);
        $avis = $em->getRepository(Avis::class)->find($avisId);
        $passager = $reservation->getUser();
        $chauffeur = $reservation->getTrajet()->getChauffeur();

        $coutTotalReservation = $reservation->getNbPlaces() * $reservation->getTrajet()->getPrixPersonne();

        if ($treatmentType =="valider") {
            $reservation->setStatut(StatutReservation::Paye);

            $chauffeur->setCredits($chauffeur->getCredits() + $coutTotalReservation);
            $passager->setCredits($chauffeur->getCredits() - $coutTotalReservation - 2);

        } elseif ($treatmentType == "equilibrer") {
            $reservation->setStatut(StatutReservation::Equilibre);

            $chauffeur->setCredits($chauffeur->getCredits() + ceil($coutTotalReservation/2));
            $passager->setCredits($passager->getCredits() - floor($coutTotalReservation/2) - 2);
        } else {
            $reservation->setStatut(StatutReservation::Rembourse);
        }

        if ($isAvisVisible == true) {
            $avis->setStatut(StatutAvis::Visible);
        } else {
            $avis->setStatut(StatutAvis::NonVisible);
        }

        $em->flush();


        return $this->redirectToRoute('app_employe');
    }
}
