<?php

namespace App\Controller;

use App\Entity\Invest;
use App\Form\InvestType;
use App\Repository\InvestRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/invest')]
class InvestController extends AbstractController
{
    #[Route('/', name: 'app_invest_index', methods: ['GET'])]
    public function index(InvestRepository $investRepository): Response
    {
        return $this->render('invest/index.html.twig', [
            'invests' => $investRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_invest_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $invest = new Invest();
        $form = $this->createForm(InvestType::class, $invest);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($invest);
            $entityManager->flush();

            return $this->redirectToRoute('app_invest_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('invest/new.html.twig', [
            'invest' => $invest,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_invest_show', methods: ['GET'])]
    public function show(Invest $invest): Response
    {
        return $this->render('invest/show.html.twig', [
            'invest' => $invest,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_invest_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Invest $invest, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(InvestType::class, $invest);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_invest_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('invest/edit.html.twig', [
            'invest' => $invest,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_invest_delete', methods: ['POST'])]
    public function delete(Request $request, Invest $invest, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$invest->getId(), $request->request->get('_token'))) {
            $entityManager->remove($invest);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_invest_index', [], Response::HTTP_SEE_OTHER);
    }
}
