<?php

namespace App\Controller;

use App\Entity\Vehicule;
use App\Entity\Photo;
use App\Entity\Commentaire;
use App\Form\VehiculeType;
use App\Form\CommentaireType;
use App\Form\DisponibiliteType;
use App\Repository\VehiculeRepository;
use App\Repository\CommentaireRepository;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

#[Route('/vehicule')]
class VehiculeController extends AbstractController
{
    #[Route('/', name: 'vehicule_index', methods: ['GET'])]
    public function index(VehiculeRepository $vehiculeRepository): Response
    {
        return $this->render('vehicule/index.html.twig', [
            'vehicules' => $vehiculeRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'vehicule_new', methods: ['GET', 'POST'])]
#[IsGranted('ROLE_ADMIN')]
public function new(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
{
    $vehicule = new Vehicule();
    $form = $this->createForm(VehiculeType::class, $vehicule);
    $form->handleRequest($request);
if ($form->isSubmitted() && $form->isValid()) {
    // Guardar la imagen principal
    $mainPhotoFile = $form->get('photo')->getData();
    if ($mainPhotoFile) {
        $originalFilename = pathinfo($mainPhotoFile->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $slugger->slug($originalFilename);
        $newFilename = $safeFilename . '-' . uniqid() . '.' . $mainPhotoFile->guessExtension();

        try {
            $mainPhotoFile->move(
                $this->getParameter('kernel.project_dir') . '/public/uploads/image',
                $newFilename
            );
            $vehicule->setPhoto($newFilename); // Aquí guardamos como string
        } catch (FileException $e) {
            $this->addFlash('danger', 'Erreur lors du téléchargement de la photo principale.');
        }
    }

    // Guardar fotos adicionales
    $photosFiles = $form->get('photos')->getData();
    if ($photosFiles) {
        foreach ($photosFiles as $photoFile) {
            $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename . '-' . uniqid() . '.' . $photoFile->guessExtension();

            try {
                $photoFile->move(
                    $this->getParameter('kernel.project_dir') . '/public/uploads/image',
                    $newFilename
                );
                $photo = new \App\Entity\Photo();
                $photo->setNom($newFilename);
                $photo->setVehicule($vehicule); // Relacionamos correctamente
                $vehicule->addPhoto($photo);
            } catch (FileException $e) {
                $this->addFlash('danger', 'Erreur lors du téléchargement des photos supplémentaires.');
            }
        }
    }

    $em->persist($vehicule);
    $em->flush();

    return $this->redirectToRoute('vehicule_index');
}


    return $this->render('vehicule/new.html.twig', [
        'form' => $form->createView(),
    ]);
}

    #[Route('/disponibles', name: 'vehicule_disponibles', methods: ['GET', 'POST'])]
    public function disponibles(Request $request, VehiculeRepository $vehiculeRepository): Response
    {
        $form = $this->createForm(DisponibiliteType::class);
        $form->handleRequest($request);

        $vehicules = [];

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $vehicules = $vehiculeRepository->findDisponiblesBetween($data['dateDebut'], $data['dateFin']);
        }

        return $this->render('vehicule/disponibles.html.twig', [
            'form' => $form->createView(),
            'vehicules' => $vehicules
        ]);
    }

    #[Route('/{id}/edit', name: 'vehicule_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(int $id, Request $request, VehiculeRepository $vehiculeRepository, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $vehicule = $vehiculeRepository->find($id);
        if (!$vehicule) {
            throw $this->createNotFoundException('Véhicule non trouvé.');
        }

        $form = $this->createForm(VehiculeType::class, $vehicule);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('photo')->getData();
            if ($file) {
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

                try {
                    $file->move(
                        $this->getParameter('kernel.project_dir') . '/public/uploads/image',
                        $newFilename
                    );
                    $vehicule->setPhoto($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('danger', 'Erreur lors du téléchargement de l\'image principale.');
                }
            }

            $files = $form->get('photos')->getData();
            if ($files) {
                foreach ($files as $file) {
                    $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFilename = $slugger->slug($originalFilename);
                    $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

                    try {
                        $file->move(
                            $this->getParameter('kernel.project_dir') . '/public/uploads/image',
                            $newFilename
                        );
                        $photo = new Photo();
                        $photo->setNom($newFilename);
                        $vehicule->addPhoto($photo);
                    } catch (FileException $e) {
                        $this->addFlash('danger', 'Erreur lors du téléchargement d\'une photo supplémentaire.');
                    }
                }
            }

            $em->flush();
            return $this->redirectToRoute('vehicule_index');
        }

        return $this->render('vehicule/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'vehicule_show', methods: ['GET', 'POST'])]
    public function show(Vehicule $vehicule, Request $request, EntityManagerInterface $em, CommentaireRepository $commentaireRepository, ReservationRepository $reservationRepository): Response
    {
        $commentaire = new Commentaire();
        $commentaire->setVehicule($vehicule);
        $commentaire->setAuteur($this->getUser());

        $canComment = false;
        if ($this->getUser() && $this->isGranted('ROLE_CLIENT')) {
            $hasReservation = $reservationRepository->findOneBy([
                'utilisateur' => $this->getUser(),
                'vehicule' => $vehicule
            ]);
            $canComment = $hasReservation !== null;
        }

        $form = $this->createForm(CommentaireType::class, $commentaire);
        $form->handleRequest($request);

        if ($canComment && $form->isSubmitted() && $form->isValid()) {
            $commentaire->setDatePublication(new \DateTimeImmutable());
            $em->persist($commentaire);
            $em->flush();

            $this->addFlash('success', 'Commentaire ajouté.');
            return $this->redirectToRoute('vehicule_show', ['id' => $vehicule->getId()]);
        }

        return $this->render('vehicule/show.html.twig', [
            'vehicule' => $vehicule,
            'commentaires' => $vehicule->getCommentaires(),
            'form' => $form->createView(),
            'canComment' => $canComment,
        ]);
    }

    #[Route('/{id}', name: 'vehicule_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, Vehicule $vehicule, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $vehicule->getId(), $request->request->get('_token'))) {
            $em->remove($vehicule);
            $em->flush();
        }

        return $this->redirectToRoute('vehicule_index');
    }
}
