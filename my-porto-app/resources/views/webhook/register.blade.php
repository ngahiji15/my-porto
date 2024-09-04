@extends('layouts.app')

@section('content')
<main>
    <section class="hero-2 justify-content-center align-items-center" id="section_3">
        <div class="container">
            <div class="col-lg-10 col-12 mx-auto">
                <div class="section-title-wrap-2 d-flex justify-content-center align-items-center">
                    <img src="https://www.logosvgpng.com/wp-content/uploads/2020/12/doku-logo-vector.png" class="avatar-image-center img-fluid" alt="">
                    <h2 class="text-black ms-4 mb-05">E-Commerce Integrated with Doku Payment Gateway</h2>
                </div>
            </div>
        </div>
    </section>
    <section class="Portofolio section-padding" id="section_3">
        <div class="container">
            <div class="row">
                <div class="col-lg-10 col-12 mx-auto">
                    <div class="section-title-wrap-2 d-flex justify-content-center align-items-center mb-5">
                        <h2 class="text-white ms-4 mb-0">
                            <i class="bi bi-cart-fill"></i> Please fill form below.</h2>
                    </div>

                    <div class="row g-5">
                        <div class="col-md-5 col-lg-4 order-md-last">
                            <h4 class="d-flex justify-content-between align-items-center mb-3">
                                <span class="text-dark">Payment Method</span>
                            </h4>
                            <form class="needs-validation" id="paymentForm" method="get" action="/proceed-payment" novalidate>
                                <ul class="list-group mb-3">
                                    <li class="list-group-item d-flex justify-content-between lh-sm">
                                        <div>
                                            <h6 class="my-0">Total Amount</h6>
                                        </div>
                                        <strong>IDR {{ number_format($totalAmount) }}</strong>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between lh-sm">
                                        <div class="payment-options">
                                            <input type="radio" id="pembayaran_a" name="pembayaran" value="A">
                                            <label for="pembayaran_a"><strong>Doku Checkout</strong></label><br>
                                            <small><i>All payment method in Doku page.</i></small><br><br>
                                            <input type="radio" id="pembayaran_b" name="pembayaran" value="B">
                                            <label for="pembayaran_b"><strong>Doku H2H Credit Card</strong></label><br>
                                            <small><i>Credit Card H2H type, form input card on your page.</i></small><br><br>
                                            <input type="radio" id="pembayaran_c" name="pembayaran" value="C">
                                            <label for="pembayaran_c"><strong>VA Danamon Direct Inquiry</strong></label><br>
                                            <small><i>Virtual Account Number Direct Inquiry type, can use static VA for payment.</i></small><br>
                                        </div>
                                    </li>
                                </ul>
                        </div>
                        <div class="col-md-7 col-lg-8">
                            <h4 class="mb-3">Billing Form</h4>
                            <form class="needs-validation" method="post" action="/process-cc-process" novalidate>
                                <div class="row g-3">
                                    <div class="col-sm-12">
                                        <label for="firstName" class="form-label">Name</label>
                                        <input type="text" class="form-control" id="firstName" placeholder="Jonh" value="" name="firstName" required>
                                        <div class="invalid-feedback">
                                            Valid first name is required.
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <label for="email" class="form-label">Email <span class="text-body-secondary"></span></label>
                                        <input type="email" class="form-control" id="email" placeholder="you@example.com" name="email">
                                        <div class="invalid-feedback">
                                            Please enter a valid email address for shipping updates.
                                        </div>
                                        <br>
                                        <br>
                                        <br>
                                    </div>
                                    <button class="w-100 btn btn-dark btn-lg" type="submit">Continue to Payment</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <script>
    document.getElementById("paymentForm").addEventListener("submit", function(event) {
        var pembayaranOptions = document.getElementsByName("pembayaran");
        var isChecked = false;
        for (var i = 0; i < pembayaranOptions.length; i++) {
            if (pembayaranOptions[i].checked) {
                isChecked = true;
                break;
            }
        }
        if (!isChecked) {
            alert("please select one of the payment methods.");
            event.preventDefault();
        }
    });
</script>
</main>
@endsection
