@extends('layouts.app')

@section('content')
<div class="container">
    <p class="m-0 text-secondary">
        Tracker:
        <a href="{{ $Organization->getSettingsRoute() }}" style="text-decoration: none">
            {{ $Organization->name }}
        </a>
    </p>

    <div>
        <h1>New Payment Method</h1>
    </div>

    @if ($errors->any())
        <div class="row mb-3">
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <div class="row">
        <div class="col-md-6">
            <form class="" id="form" method="POST">
                @csrf

                <input type="hidden" id="stripe_payment_method" name="stripe_payment_method">

                <label for="name" class="form-label">Cardholder Name</label>

                <input id="card-holder-name" type="text" class="form-control"
                id="name" name="name" required>

                <div class="my-3">
                    <label for="card-element" class="form-label">Credit or Debit Card</label>

                    <!-- Stripe Elements Placeholder -->
                    <div id="card-element"
                    style="height: 2.7em; padding-top: .7em;"
                    class="form-control"></div>
                </div>

                <button id="card-button" class="btn btn-primary mt-1" data-secret="{{ $intent->client_secret }}">
                    Set Payment Method
                </button>
            </form>
        </div>
    </div>
</div>

    <script src="https://js.stripe.com/v3/"></script>

    <script>
        const stripe = Stripe('{{ env('STRIPE_KEY') }}');

        const elements = stripe.elements();
        const cardElement = elements.create('card');

        cardElement.mount('#card-element');

        const cardHolderName = document.getElementById('card-holder-name');
        const cardButton = document.getElementById('card-button');
        const clientSecret = cardButton.dataset.secret;

        cardButton.addEventListener('click', async (e) => {
            cardButton.disabled = true;

            const { setupIntent, error } = await stripe.confirmCardSetup(
                clientSecret, {
                    payment_method: {
                        card: cardElement,
                        billing_details: { name: cardHolderName.value }
                    }
                }
            );

            if (error) {
                console.log(error);
                alert(error.message);
                cardButton.disabled = false;
                // Display "error.message" to the user...
            } else {
                // you may pass the resulting setupIntent.payment_method identifier to your Laravel application
                document.getElementById('stripe_payment_method').value = setupIntent.payment_method;
                document.getElementById('form').submit();
            }
        });
    </script>
@endsection
