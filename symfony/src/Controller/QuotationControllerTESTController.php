<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class QuotationControllerTESTController extends AbstractController
{
    #[Route('/quotation/controller/t/e/s/t', name: 'app_quotation_controller_t_e_s_t')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/QuotationControllerTESTController.php',
        ]);
    }
}
