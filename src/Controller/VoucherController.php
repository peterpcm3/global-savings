<?php

namespace App\Controller;

use App\Entity\Voucher;
use App\Repository\VoucherRepository;
use Doctrine\Persistence\ManagerRegistry;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class VoucherController
 *
 * @package App\Controller
 */
#[Route('/api', name: 'restapi')]
class VoucherController extends AbstractController
{
    /**
     * @var VoucherRepository $voucherRepository
     */
    private VoucherRepository $voucherRepository;

    /**
     * @var Serializer $jmsSerializer
     */
    private Serializer $jmsSerializer;

    public function __construct(VoucherRepository $voucherRepository)
    {
        $this->voucherRepository = $voucherRepository;
        $this->jmsSerializer = SerializerBuilder::create()->build();
    }

    /**
     * @param ManagerRegistry $doctrine
     * @param string $fetch
     *
     * @return JsonResponse
     */
    #[Route('/voucher/{fetch}', name: 'app_voucher', requirements: ['fetch' => 'active|expired'], methods: ['GET'])]
    public function index(ManagerRegistry $doctrine, string $fetch = 'active'): JsonResponse
    {

        if ('expired' === $fetch) {
            $vouchers = $this->voucherRepository->findExpiredVouchers();
        }
        else {
            $vouchers = $this->voucherRepository->findActiveVouchers();
        }

        $vouchers = $this->jmsSerializer->toArray($vouchers);

        return $this->json($vouchers);
    }

    /**
     * @param ManagerRegistry $doctrine
     * @param Request $request
     *
     * @return JsonResponse
     */
    #[Route('/voucher', name: 'app_create_voucher', methods: ['POST'])]
    public function create(ManagerRegistry $doctrine, Request $request): JsonResponse
    {
        $entityManager = $doctrine->getManager();

        $expireDate = $request->request->get('expire_date');
        if (null === $expireDate || !strtotime($expireDate)) {
            return $this->json('Wrong date time parameter', JsonResponse::HTTP_BAD_REQUEST);
        }

        try {
            $expireDateTime = new \DateTime($expireDate);
        }
        catch (\Exception) {
            return $this->json('Wrong datetime', JsonResponse::HTTP_BAD_REQUEST);
        }

        $amount = $request->request->get('amount');
        if (!is_numeric($amount)) {
            return $this->json('Wrong amount parameter', JsonResponse::HTTP_BAD_REQUEST);
        }

        $voucher = new Voucher();
        $voucher->setAmount($amount);
        $voucher->setIsUsed(false);
        $voucher->setExpireDate($expireDateTime);

        $entityManager->persist($voucher);
        $entityManager->flush();

        return $this->json($voucher->toArray());
    }

    /**
     * @param ManagerRegistry $doctrine
     * @param Voucher $voucher
     * @param Request $request
     *
     * @return JsonResponse
     */
    #[Route('/voucher/{id}', name: 'app_edit_voucher', methods: ['PUT'])]
    public function edit(ManagerRegistry $doctrine, Voucher $voucher, Request $request): JsonResponse
    {
        $entityManager = $doctrine->getManager();

        $expireDate = $request->request->get('expire_date');
        if (null !== $expireDate) {
            if (!strtotime($expireDate)) {
                return $this->json('Wrong date time parameter', JsonResponse::HTTP_BAD_REQUEST);
            }

            try {
                $expireDateTime = new \DateTime($expireDate);
            }
            catch (\Exception) {
                return $this->json('Wrong datetime', JsonResponse::HTTP_BAD_REQUEST);
            }

            $voucher->setExpireDate($expireDateTime);
        }

        $amount = $request->request->get('amount');
        if (null !== $amount) {
            if (!is_numeric($amount)) {
                return $this->json('Wrong amount parameter', JsonResponse::HTTP_BAD_REQUEST);
            }

            $voucher->setAmount($amount);
        }

        $isUsed = $request->request->get('is_used');
        if (null !== $isUsed) {
            if (!is_bool($isUsed)) {
                return $this->json('Wrong isUsed parameter', JsonResponse::HTTP_BAD_REQUEST);
            }

            $voucher->setIsUsed($isUsed);
        }

        $entityManager->persist($voucher);
        $entityManager->flush();

        return $this->json($voucher->toArray());
    }

    /**
     * @param ManagerRegistry $doctrine
     * @param Voucher $voucher
     *
     * @return JsonResponse
     */
    #[Route('/voucher/{id}', name: 'app_delete_voucher', methods: ['DELETE'])]
    public function delete(ManagerRegistry $doctrine, Voucher $voucher): JsonResponse
    {
        $entityManager = $doctrine->getManager();

        $entityManager->remove($voucher);
        $entityManager->flush();

        return $this->json('Deleted');
    }
}
