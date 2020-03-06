<?php
declare(strict_types=1);

namespace App\Controller;

use App\Exception\AmountNotConverted;
use App\Service\Exchanger;
use Money\Currency;
use Money\Money;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class ExchangeRatesController extends AbstractController
{
    /**
     * @param Request $request
     * @param Exchanger $exchanger
     * @return JsonResponse
     * @throws AmountNotConverted
     * @Route("/exchange")
     */
    public function show(Request $request, Exchanger $exchanger): JsonResponse
    {
        $from = (string)$request->query->get('from');
        $to = (string)$request->query->get('to');
        $amount = (int)(100 * $request->query->get('amount', 1.0));

        $moneyFrom = new Money($amount, new Currency($from));
        $currencyTo = new Currency($to);

        $result = $exchanger->exchange($moneyFrom, $currencyTo);

        return new JsonResponse($result->getAmount() / 100);
    }
}
