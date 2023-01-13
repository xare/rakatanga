<?php 

namespace App\Service;

use App\Entity\Reservation;
use Stripe\Checkout\Session as CheckoutSession;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use App\Service\localizationHelper;

class paymentHelper {

  public function __construct(
    private localizationHelper $localizationHelper,
    private UrlGeneratorInterface $UrlGenerator,
    private string $stripeSecretKey,
  )
  {
    
  }

  public function createStripeCheckout( 
                        Reservation $reservation, 
                        string $locale,
                        $formData, 
                        $ammount){
    \Stripe\Stripe::setApiKey($this->stripeSecretKey);

    return CheckoutSession::create([
                  'payment_method_types' => ['card'],
                  'mode' => 'payment',
                  'locale' => $locale,
                  'line_items' => [
                      [
                          'price_data' => [
                              'currency' => 'eur',
                              'product_data' => [
                                  'name' => $this->localizationHelper->renderTravelString($reservation->getDate()->getTravel()->getId(),$locale),
                                  'metadata' => [
                                      'paymentType' => $formData['paymentMethod'],
                                  ],
                              ],
                              'unit_amount' => $ammount * 100,
                          ],
                          'quantity' => 1,
                      ],
                  ],

                  'success_url' => $this->UrlGenerator->generate(
                      'success_url',
                      [
                          'reservation' => $reservation->getId(),
                          'ammount' => $ammount,
                          '_locale' => $locale,
                          // 'session_id'=>'{CHECKOUT_SESSION_ID}'
                      ],
                      UrlGeneratorInterface::ABSOLUTE_URL).'&session_id={CHECKOUT_SESSION_ID}',
                  'cancel_url' => $this->UrlGenerator->generate(
                      'cancel_url',
                      [
                          'reservation' => $reservation->getId(),
                          '_locale' => $locale,
                      ],
                      UrlGeneratorInterface::ABSOLUTE_URL),
                ]);
  }
}