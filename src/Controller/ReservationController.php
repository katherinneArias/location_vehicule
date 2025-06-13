<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Form\ReservationType;
use App\Repository\ReservationRepository;
use App\Repository\VehiculeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

#[Route('/reservation')]
class ReservationController extends AbstractController
{
    #[Route('/', name: 'app_reservation_index', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function index(ReservationRepository $reservationRepository): Response
    {
        $user = $this->getUser();
        $roles = $user->getRoles();

        if (in_array('ROLE_ADMIN', $roles)) {
            $reservations = $reservationRepository->findAll();
        } else {
            $reservations = $reservationRepository->findBy(['utilisateur' => $user]);
        }

        return $this->render('reservation/index.html.twig', [
            'reservations' => $reservations,
        ]);
    }

    #[Route('/new/{vehicule}', name: 'app_reservation_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_CLIENT')]
    public function new(
        Request $request,
        EntityManagerInterface $em,
        VehiculeRepository $vehiculeRepository,
        ?int $vehicule = null
    ): Response {
        $reservation = new Reservation();
        $reservation->setUtilisateur($this->getUser());

        if ($vehicule) {
            $vehiculeEntity = $vehiculeRepository->find($vehicule);
            if (!$vehiculeEntity) {
                throw $this->createNotFoundException('Véhicule non trouvé.');
            }
            $reservation->setVehicule($vehiculeEntity);
        }

        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $diff = $reservation->getDateDebut()->diff($reservation->getDateFin())->days;
            $prixParJour = $reservation->getVehicule()->getPrixParJour();

            $prixTotal = $diff * $prixParJour;

            if ($diff > 10) {
                $prixTotal *= 0.9;
                $this->addFlash('info', 'Un descuento del 10% ha sido aplicado por una reserva de más de 10 días.');
            }

            $reservation->setPrixTotal($prixTotal);

            $em->persist($reservation);
            $em->flush();

            return $this->redirectToRoute('app_reservation_index');
        }

        return $this->render('reservation/new.html.twig', [
            'form' => $form->createView(),
            'reservation' => $reservation
        ]);
    }

    #[Route('/{id}/edit', name: 'app_reservation_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_CLIENT')]
    public function edit(Request $request, Reservation $reservation, EntityManagerInterface $em): Response
    {
        if ($reservation->getUtilisateur() !== $this->getUser()) {
            throw $this->createAccessDeniedException('No tienes permiso para modificar esta reserva.');
        }

        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $diff = $reservation->getDateDebut()->diff($reservation->getDateFin())->days;
            $prixParJour = $reservation->getVehicule()->getPrixParJour();

            $prixTotal = $diff * $prixParJour;

            if ($diff > 10) {
                $prixTotal *= 0.9;
                $this->addFlash('info', 'Un descuento del 10% ha sido aplicado por una reserva de más de 10 días.');
            }

            $reservation->setPrixTotal($prixTotal);
            $em->flush();

            return $this->redirectToRoute('app_reservation_index');
        }

        return $this->render('reservation/edit.html.twig', [
            'form' => $form->createView(),
            'reservation' => $reservation,
        ]);
    }

    #[Route('/{id}', name: 'app_reservation_delete', methods: ['POST'])]
    #[IsGranted('ROLE_CLIENT')]
    public function delete(Request $request, Reservation $reservation, EntityManagerInterface $em): Response
    {
        if ($reservation->getUtilisateur() !== $this->getUser()) {
            throw $this->createAccessDeniedException('No puedes eliminar esta reserva.');
        }

        if ($this->isCsrfTokenValid('delete' . $reservation->getId(), $request->request->get('_token'))) {
            $em->remove($reservation);
            $em->flush();
        }

        return $this->redirectToRoute('app_reservation_index');
    }
}
