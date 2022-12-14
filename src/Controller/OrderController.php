<?php

namespace App\Controller;

use App\Entity\Order;
use App\Repository\OrderRepository;
use App\Repository\VoucherRepository;
use Doctrine\Persistence\ManagerRegistry;
use JMS\Serializer\SerializerBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class OrderController
 *
 * @package App\Controller
 *
 * @Route("/api", name="restapi")
 */
class OrderController extends AbstractController
{
    /**
     * @param OrderRepository $orderRepository
     * @param Request $request
     *
     * @return JsonResponse
     * @throws \Exception
     */
    #[Route('/order', name: 'app_order', methods: ['GET'])]
    public function index(OrderRepository $orderRepository, Request $request): JsonResponse
    {
        $currentPage = (int) $request->query->get('page', 1);

        $orders = $orderRepository->getPaginatedOrders($request->query->get('page', 1));
        $totalResult = $orders->count();
        $iterator = $orders->getIterator();

        $jmsSerializer = SerializerBuilder::create()->build();
        $orders = $jmsSerializer->toArray($iterator);

        return $this->json([
            'page'  => $currentPage,
            'total' => $totalResult,
            'totalPages' => ceil($totalResult/OrderRepository::ORDERS_PER_PAGE),
            'data'  => $orders
        ]);
    }

    /**
     * @param ManagerRegistry $doctrine
     * @param Request $request
     * @param VoucherRepository $voucherRepository
     *
     * @return JsonResponse
     */
    #[Route('/order', name: 'app_create_order', methods: ['POST'])]
    public function create(ManagerRegistry $doctrine, Request $request, VoucherRepository $voucherRepository): JsonResponse
    {
        $entityManager = $doctrine->getManager();

        $originalAmount = $request->request->get('amount');
        if (!is_numeric($originalAmount)) {
            return $this->json('Wrong amount parameter', JsonResponse::HTTP_BAD_REQUEST);
        }

        $voucher = null;
        $amount = $originalAmount;
        $voucherId = $request->request->get('voucher_id');
        if (null !== $voucherId) {
            $voucher = $voucherRepository->findOneBy(['id' => $voucherId]);
            if (null === $voucher) {
                return $this->json('Wrong voucher parameter', JsonResponse::HTTP_BAD_REQUEST);
            }

            $now = new \DateTime();
            if (true === $voucher->isIsUsed() || $now > $voucher->getExpireDate()) {
                return $this->json('Voucher is expired', JsonResponse::HTTP_BAD_REQUEST);
            }

            $amount = $originalAmount - $voucher->getAmount();
        }

        $order = new Order();
        $order->setOriginalAmount($originalAmount);
        $order->setAmount($amount);
        $order->setVoucher($voucher);

        $entityManager->persist($order);
        $entityManager->flush();

        return $this->json($order->toArray());
    }
}
