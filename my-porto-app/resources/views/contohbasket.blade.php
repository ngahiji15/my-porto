
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
                            <i class="bi bi-cart-fill"></i> Please select product you want to checkout.</h2>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 col-12">
                    <div class="projects-thumb">
                        <div class="projects-info">
                            <small class="projects-tag">Zoik agency</small>
                            <h3 class="projects-title">Zoik agency</h3>
                        </div>
                        <a href="images/projects/nikhil-KO4io-eCAXA-unsplash.jpg" class="popup-image">
                            <img src="images/projects/nikhil-KO4io-eCAXA-unsplash.jpg" class="projects-image img-fluid" alt="">
                        </a>
                        <button class="add-to-cart btn" data-id="A">Add to Cart</button>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 col-12">
                    <div class="projects-thumb">
                        <div class="projects-info">
                            <small class="projects-tag">The Watch</small>
                            <h3 class="projects-title">The Watch</h3>
                        </div>
                        <a href="images/projects/the-5th-IQYR7N67dhM-unsplash.jpg" class="popup-image">
                            <img src="images/projects/the-5th-IQYR7N67dhM-unsplash.jpg" class="projects-image img-fluid" alt="">
                        </a>
                        <button class="add-to-cart btn" data-id="B">Add to Cart</button>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 col-12">
                    <div class="projects-thumb">
                        <div class="projects-info">
                            <small class="projects-tag">Polo</small>
                            <h3 class="projects-title">Polo</h3>
                        </div>
                        <a href="images/projects/true-agency-9Bjog5FZ-oc-unsplash.jpg" class="popup-image">
                            <img src="images/projects/true-agency-9Bjog5FZ-oc-unsplash.jpg" class="projects-image img-fluid" alt="">
                        </a>
                        <button class="add-to-cart btn" data-id="C">Add to Cart</button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="cart-items">
    <div class="container">
        <div class="row">
            <div class="col-lg-10 col-12 mx-auto">
                <h2>Shopping Cart</h2>
                <div id="cart-items"></div>
                <button id="empty-cart" class="btn">Empty Cart</button>
            </div>
        </div>
    </div>
</section>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const cartItemsElement = document.getElementById('cart-items');
        const emptyCartButton = document.getElementById('empty-cart');
        const addToCartButtons = document.querySelectorAll('.add-to-cart');
        let cart = {};

        // Initialize cart from localStorage
        if (localStorage.getItem('cart')) {
            cart = JSON.parse(localStorage.getItem('cart'));
            updateCartUI();
        }

        // Add event listeners
        addToCartButtons.forEach(button => {
            button.addEventListener('click', addToCart);
        });

        emptyCartButton.addEventListener('click', emptyCart);

        function addToCart(event) {
            const productId = event.target.getAttribute('data-id');
            if (cart[productId]) {
                cart[productId]++;
            } else {
                cart[productId] = 1;
            }
            updateCartUI();
            saveCartToLocalStorage();
        }

        function removeFromCart(productId) {
            delete cart[productId];
            updateCartUI();
            saveCartToLocalStorage();
        }

        function emptyCart() {
            cart = {};
            updateCartUI();
            saveCartToLocalStorage();
        }

        function updateCartUI() {
            cartItemsElement.innerHTML = '';
            for (let productId in cart) {
                const productElement = document.createElement('div');
                productElement.innerHTML = `
                    <p>Product ${productId} - Quantity: 
                    <button class="decrease-quantity btn" data-id="${productId}">-</button>
                    ${cart[productId]}
                    <button class="increase-quantity btn" data-id="${productId}">+</button>
                    </p>
                    <button class="remove-from-cart btn" data-id="${productId}">Remove</button>
                `;
                cartItemsElement.appendChild(productElement);
            }
        }

        // Event delegation for remove, increase, and decrease quantity buttons
        cartItemsElement.addEventListener('click', function(event) {
            const productId = event.target.getAttribute('data-id');
            if (event.target.classList.contains('remove-from-cart')) {
                removeFromCart(productId);
            } else if (event.target.classList.contains('increase-quantity')) {
                cart[productId]++;
                updateCartUI();
                saveCartToLocalStorage();
            } else if (event.target.classList.contains('decrease-quantity')) {
                if (cart[productId] > 1) {
                    cart[productId]--;
                } else {
                    delete cart[productId];
                }
                updateCartUI();
                saveCartToLocalStorage();
            }
        });

        // Function to save cart data to localStorage
        function saveCartToLocalStorage() {
            localStorage.setItem('cart', JSON.stringify(cart));
        }
    });
</script>
</main>

@endsection
