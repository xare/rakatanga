<?php

namespace App\Service;

use App\Entity\Reservation;
use App\Entity\Reservations;

class StripeService
{
    private $privateKey;

    public function __construct()
    {
        if ($_ENV['APP_ENV'] === 'dev') {
            $this->privateKey = $_ENV['STRIPE_SECRET_KEY_TEST'];
        } else {
            $this->privateKey = $_ENV['STRIPE_SECRET_KEY_LIVE'];
        }
    }

    public function paymentIntent(Reservations $reservation)
    {
        \Stripe\Stripe::setApiKey($this->privateKey);

        return \Stripe\PaymentIntent::create([
            'amount' => $reservation->getTotalttc() * 100,
            'currency' => 'eur',
            'payment_method_types' => [
                'card',
            ],
        ]);
    }

    public function payment(
        $amount,
        $currentcy,
        $description,
        array $stripeParameters
    ) {
        \Stripe\Stripe::setApiKey($this->privateKey);
        $payment_intent = null;

        if (isset($stripeParameters['stripeIntentId'])) {
            $payment_intent = \Stripe\PaymentIntent::retrieve($stripeParameters['stripeIntentId']);
        }

        if ($stripeParameters['stripeIntentId'] === 'succeeded') {
            // TODO
        } else {
            $payment_intent->cancel();
        }

        return $payment_intent;
    }

    public function stripe(array $stripeParameters, Reservation $reservation)
    {
        return $this->payment(
            $reservation->getTotalAmmount() * 100,
            'eur',
            $reservation->getId(),
            $stripeParameters
        );
    }
}
