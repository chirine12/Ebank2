<?php

namespace App\Controller;

use App\Entity\Typetaux;
use App\Form\TypetauxType;
use App\Repository\TypetauxRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/typetaux')]
class TypetauxController extends AbstractController
{
    #[Route('/', name: 'app_typetaux_index', methods: ['GET'])]
    public function index(TypetauxRepository $typetauxRepository): Response
    {
        return $this->render('typetaux/index.html.twig', [
            'typetauxes' => $typetauxRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_typetaux_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $typetaux = new Typetaux();
        $form = $this->createForm(TypetauxType::class, $typetaux);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($typetaux);
            $entityManager->flush();

            return $this->redirectToRoute('app_typetaux_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('typetaux/new.html.twig', [
            'typetaux' => $typetaux,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_typetaux_show', methods: ['GET'])]
    public function show(Typetaux $typetaux): Response
    {
        return $this->render('typetaux/show.html.twig', [
            'typetaux' => $typetaux,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_typetaux_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Typetaux $typetaux, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TypetauxType::class, $typetaux);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_typetaux_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('typetaux/edit.html.twig', [
            'typetaux' => $typetaux,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_typetaux_delete', methods: ['POST'])]
    public function delete(Request $request, Typetaux $typetaux, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$typetaux->getId(), $request->request->get('_token'))) {
            $entityManager->remove($typetaux);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_typetaux_index', [], Response::HTTP_SEE_OTHER);
    }
}
