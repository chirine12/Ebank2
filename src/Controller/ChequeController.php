<?php

namespace App\Controller;
use App\Services\Yousignservice;
use App\Entity\Cheque;
use App\Form\ChequeType;
use App\Repository\ChequeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Cheque\HttpClientInterface;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;



#[Route('/cheque')]
class ChequeController extends AbstractController
{
    #[Route('/', name: 'app_cheque_index', methods: ['GET'])]
    public function index(ChequeRepository $chequeRepository): Response
    {
        return $this->render('cheque/index.html.twig', [
            'cheques' => $chequeRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_cheque_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $cheque = new Cheque();
        $form = $this->createForm(ChequeType::class, $cheque);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid())
       {    
        $uploadedFile = $form['signature']->getData();
        $newFilename = md5(uniqid()) . '.' . $uploadedFile->guessExtension();
        $uploadedDirectory = $this->getParameter('kernel.project_dir') . '/public/uploads';
        $uploadedFile->move($uploadedDirectory,$newFilename);
        $cheque->setSignature('uploads/' . $newFilename);
        $entityManager->persist($cheque);
        $entityManager->flush();
        return $this->redirectToRoute('app_cheque_index', [], Response::HTTP_SEE_OTHER);
        }
    
        return $this->renderForm('cheque/new.html.twig', [
            'cheque' => $cheque,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_cheque_show', methods: ['GET'])]
    public function show(Cheque $cheque): Response
    {
        return $this->render('cheque/show.html.twig', [
            'cheque' => $cheque,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_cheque_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Cheque $cheque, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ChequeType::class, $cheque);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_cheque_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('cheque/edit.html.twig', [
            'cheque' => $cheque,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_cheque_delete', methods: ['POST'])]
    public function delete(Request $request, Cheque $cheque, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$cheque->getId(), $request->request->get('_token'))) {
            $entityManager->remove($cheque);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_cheque_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/pdf', name: 'app_cheque_pdf', methods: ['GET'])]
    
    public function pdf(Request $request, Cheque $cheque, EntityManagerInterface $entityManager): Response
    {
        // Initialiser Dompdf
        $dompdf = new Dompdf();
        
    
        // Générer le HTML du PDF
        $html = $this->renderView('cheque/pdf.html.twig', [
            'cheque' => $cheque,
            
        ]);
    
        // Charger le HTML dans Dompdf
        $dompdf->loadHtml($html);
    
        // Définir les options du PDF (facultatif)
        $dompdf->setPaper('A4', 'portrait');
    
        // Générer le PDF
        $dompdf->render();
    
        // Obtenir le contenu du PDF
        $output = $dompdf->output();
    
        // Générer le nom du fichier
        $filename = 'cheque_' . $cheque->getId() . '.pdf';
    
        // Déterminer le chemin du fichier
        $file = $this->getParameter('kernel.project_dir') . '/public/' . $filename;
    
       
    
        // Mettre à jour le chèque avec le nom du fichier (facultatif)
        $cheque->setpdfSansSignature($filename);
        $entityManager->persist($cheque);
        $entityManager->flush(); // Flush to execute the queries
        
        file_put_contents($file, $output);
    
        // Rediriger vers la liste des chèques
        return $this->redirectToRoute('app_cheque_index', [], Response::HTTP_SEE_OTHER);
    }
}

