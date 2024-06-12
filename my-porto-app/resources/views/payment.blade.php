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
                        <h2 class="text-white ms-4 mb-0"> {{ $status == 'SUCCESS' ? 'Thank you for your payment' : 'Please complete your payment' }}
                        </h2>
                    </div>
                </div>

                @if($cartItems == null)
                    <div class="col-lg-10 col-12 mx-auto">
                        <div class="alert alert-warning text-center">
                            <p>Your Session has ended, please click button below for create a new transaction.</p>
                            <a href="{{ url('/') }}" class="btn btn-black">Home</a>
                        </div>
                    </div>
                @else
                    <div class="col-lg-10 col-12 mx-auto">
                        <div class="card mb-4 shadow-sm rounded">
                            <div class="card-body">
                                <h5 class="card-title">Order Details</h5>
                                <table class="table table-borderless">
                                    <tbody>
                                        <tr>
                                            <th>Invoice Number</th>
                                            <td>{{ $invoice }}</td>
                                        </tr>
                                        <tr>
                                            <th>{{ $status == 'SUCCESS' ? 'Payment Date' : 'Expired Date' }}</th>
                                            <td>{{ $status == 'SUCCESS' ? $expiredDate : $expiredDate }}</td>
                                        </tr>
                                        <tr>
                                            <th>Customer Name</th>
                                            <td>{{ $name }}</td>
                                        </tr>
                                        <tr>
                                            <th>Payment Status</th>
                                            <td>{{ $status }}</td>
                                        </tr>
                                        <tr class="no-margin-bottom">
                                            <th>Payment Method</th>
                                            <td>{{ $method }}</td>
                                        </tr>
                                        @if($method == 'DIPC')
                                        <tr>
                                            <th>VA Number</th>
                                            <td>12345</td>
                                        </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>

                            <div class="card-body border-top">
                                <h5 class="card-title">Products</h5>
                                <div id="cart-items">
                                    @foreach($cartItems as $productName => $item)
                                    <div class="cart-item mb-3 d-flex align-items-center">
                                        <img src="{{ $item['src'] }}" alt="Product Image" class="img-fluid me-3 rounded" style="width: 80px;">
                                        <div class="cart-item-details">
                                            <h6 class="cart-item-title mb-1">{{ $productName }}</h6>
                                            <p class="cart-item-quantity mb-1">Quantity: {{ $item['quantity'] }}</p>
                                            <p class="cart-item-amount mb-1">Amount: IDR {{ number_format($item['amount'], 0, ',', '.') }}</p>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                <div id="total-amount" class="mt-4">
                                    <h5 class="text-black">Total Amount: IDR {{ number_format($totalAmount, 0, ',', '.') }}</h5>
                                </div>
                            </div>

                            @if($status != 'SUCCESS')
                            <div class="card-body border-top text-center">
                                @if($method == 'Doku Checkout')
                                <a class="btn btn-black w-100" id="checkoutButton">Make Payment</a>
                                <small class="d-block mt-2"><cite>click <a href="https://sandbox.doku.com/integration/simulator/" target="_blank"><strong>here</strong></a> to simulate this payment</cite></small>
                                @endif
                                @if($method == 'DIPC')
                                <small class="d-block mt-2"><cite>click <a href="https://sandbox.doku.com/integration/simulator/" target="_blank"><strong>here</strong></a> to simulate this payment</cite></small>
                                @endif
                            </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </section>
</main>

<script src="https://sandbox.doku.com/jokul-checkout-js/v1/jokul-checkout-1.0.0.js"></script>
<script>
    document.getElementById('checkoutButton').addEventListener('click', function () {
        loadJokulCheckout('{{ $urlCheckout }}');
    });

    @if($status == 'PENDING')
    let expiredDate = new Date('{{ $expiredDate }}');
    let timeLeft = Math.floor((expiredDate - new Date()) / (1000 * 60)); 

    if (timeLeft > 0) {
        setTimeout(function() {
            alert('Sorry, your payment time has expired. Please click the home button below to make the new transaction from the start, Thank you');
            location.href = "/"; 
        }, timeLeft * 60 * 1000); 
    } else {
        alert('Sorry, your payment time has expired. Please click the home button below to make the new transaction from the start, Thank you');
        location.href = "/"; 
    }
    @endif
</script>

<style>
    .card {
        border: none;
    }
    .card-body {
        padding: 2rem;
    }
    .card-title {
        font-weight: bold;
        font-size: 1.25rem;
    }
    .table th, .table td {
        vertical-align: middle;
        padding: 0.25rem 0.75rem;
    }
    .table th {
        width: 40%;
        color: #6c757d;
    }
    .no-margin-bottom {
        margin-bottom: 0;
    }
    .btn-black {
        background-color: #000;
        color: #fff;
        border: none;
    }
    .btn-black:hover {
        background-color: #333;
        color: #fff;
    }
    cite {
        font-size: 0.875rem;
    }
</style>
@endsection
