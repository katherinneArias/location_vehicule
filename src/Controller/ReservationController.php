<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Form\ReservationType;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/reservation")
 */
class ReservationController extends AbstractController
{
    /**
     * @Route("/", name="app_reservation_index", methods={"GET"})
     * @IsGranted("ROLE_CLIENT")
     */
    public function index(ReservationRepository $reservationRepository): Response
    {
        $user = $this->getUser();
        $reservations = $reservationRepository->findBy(['utilisateur' => $user]);

        return $this->render('reservation/index.html.twig', [
            'reservations' => $reservations,
        ]);
    }

    /**
     * @Route("/new", name="app_reservation_new", methods={"GET", "POST"})
     * @IsGranted("ROLE_CLIENT")
     */
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $reservation = new Reservation();
        $reservation->setUtilisateur($this->getUser());

        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $diff = $reservation->getDateDebut()->diff($reservation->getDateFin())->days;
            $prixParJour = $reservation->getVehicule()->getPrixParJour();
            $reservation->setPrixTotal($diff * $prixParJour);

            $em->persist($reservation);
            $em->flush();

            return $this->redirectToRoute('app_reservation_index');
        }

        return $this->render('reservation/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="app_reservation_edit", methods={"GET", "POST"})
     * @IsGranted("ROLE_CLIENT")
     */
    public function edit(Request $request, Reservation $reservation, EntityManagerInterface $em): Response
    {
        if ($reservation->getUtilisateur() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You do not own this reservation.');
        }

        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $diff = $reservation->getDateDebut()->diff($reservation->getDateFin())->days;
            $prixParJour = $reservation->getVehicule()->getPrixParJour();
            $reservation->setPrixTotal($diff * $prixParJour);

            $em->flush();

            return $this->redirectToRoute('app_reservation_index');
        }

        return $this->render('reservation/edit.html.twig', [
            'form' => $form->createView(),
            'reservation' => $reservation,
        ]);
    }

    /**
     * @Route("/{id}", name="app_reservation_delete", methods={"POST"})
     * @IsGranted("ROLE_CLIENT")
     */
    public function delete(Request $request, Reservation $reservation, EntityManagerInterface $em): Response
    {
        if ($reservation->getUtilisateur() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You do not own this reservation.');
        }

        if ($this->isCsrfTokenValid('delete' . $reservation->getId(), $request->request->get('_token'))) {
            $em->remove($reservation);
            $em->flush();
        }

        return $this->redirectToRoute('app_reservation_index');
    }
}

