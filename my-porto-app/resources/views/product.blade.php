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
                        <h2 class="text-white ms-4 mb-0">Please select product you want to checkout</h2>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 col-12">
                    <div class="projects-thumb">
                        <div class="projects-info">
                            <small class="projects-tag text-black">IDR 120,000</small>
                            <h3 class="projects-title">Zoik Drink</h3>
                        </div>
                        <a href="images/projects/nikhil-KO4io-eCAXA-unsplash.jpg" class="popup-image">
                            <img src="images/projects/nikhil-KO4io-eCAXA-unsplash.jpg" class="projects-image img-fluid" alt="">
                        </a>
                        <button class="add-to-cart btn mt-3" data-id="Zoik Drink" data-amount="120000" data-src="images/projects/nikhil-KO4io-eCAXA-unsplash.jpg">Add to Cart</button>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 col-12">
                    <div class="projects-thumb">
                        <div class="projects-info">
                            <small class="projects-tag text-black">IDR 56,000</small>
                            <h3 class="projects-title">The Watch</h3>
                        </div>
                        <a href="images/projects/the-5th-IQYR7N67dhM-unsplash.jpg" class="popup-image">
                            <img src="images/projects/the-5th-IQYR7N67dhM-unsplash.jpg" class="projects-image img-fluid" alt="">
                        </a>
                        <button class="add-to-cart btn mt-3" data-id="The Watch" data-amount="55000" data-src="images/projects/the-5th-IQYR7N67dhM-unsplash.jpg">Add to Cart</button>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 col-12">
                    <div class="projects-thumb">
                        <div class="projects-info">
                            <small class="projects-tag text-black">IDR 12,000</small>
                            <h3 class="projects-title">Polo</h3>
                        </div>
                        <a href="images/projects/true-agency-9Bjog5FZ-oc-unsplash.jpg" class="popup-image">
                            <img src="images/projects/true-agency-9Bjog5FZ-oc-unsplash.jpg" class="projects-image img-fluid" alt="">
                        </a>
                        <button class="add-to-cart btn mt-3" data-id="Polo" data-amount="12000" data-src="images/projects/true-agency-9Bjog5FZ-oc-unsplash.jpg">Add to Cart</button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Cart items list -->
    <section class="cart-items">
        <div class="container mb-5">
            <div class="row">
                <div class="col-lg-10 col-12 mx-auto">
                    <h2 class="text-black">Shopping Cart</h2>
                    <div id="cart-items"></div>
                    <div id="total-amount"></div>
                    <button id="empty-cart" class="btn">Empty Cart</button>
                    <button id="checkout" class="btn">Checkout</button>
                </div>
            </div>
        </div>
    </section>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const cartItemsElement = document.getElementById('cart-items');
        const totalAmountElement = document.getElementById('total-amount');
        const emptyCartButton = document.getElementById('empty-cart');
        const addToCartButtons = document.querySelectorAll('.add-to-cart');
        const checkoutButton = document.getElementById('checkout');
        let cart = {};

        // Clear the cart when the page loads
        localStorage.removeItem('cart');

        // Initialize cart from localStorage (if it still exists for some reason)
        if (localStorage.getItem('cart')) {
            cart = JSON.parse(localStorage.getItem('cart'));
            updateCartUI();
        }

        // Add event listeners
        addToCartButtons.forEach(button => {
            button.addEventListener('click', addToCart);
        });

        emptyCartButton.addEventListener('click', emptyCart);
        checkoutButton.addEventListener('click', goToCheckout);

        // Function to calculate total amount for each product
        function calculateProductTotal(productId) {
            const { quantity, amount } = cart[productId];
            return quantity * amount;
        }

        function addToCart(event) {
            const productId = event.target.getAttribute('data-id');
            const amount = parseInt(event.target.getAttribute('data-amount'));
            const src = event.target.getAttribute('data-src');
            if (cart[productId]) {
                cart[productId].quantity++;
            } else {
                cart[productId] = { quantity: 1, amount: amount, src: src };
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
            let totalAmount = 0;
            for (let productId in cart) {
                const { quantity, amount, src } = cart[productId];
                totalAmount += calculateProductTotal(productId);
                const productElement = document.createElement('div');
                productElement.classList.add('cart-item');
                productElement.innerHTML = `
                    <img src="${src}" alt="Product Image">
                    <div class="cart-item-details">
                        <h3 class="cart-item-title">${productId}</h3>
                        <p class="cart-item-quantity">Quantity : 
                            <button class="decrease-quantity btn" data-id="${productId}">-</button>
                            ${quantity}
                            <button class="increase-quantity btn" data-id="${productId}">+</button>
                        </p>
                        <p class="cart-item-amount">Amount : IDR ${calculateProductTotal(productId).toLocaleString()}</p>
                    </div>
                    <button class="cart-item-remove btn" data-id="${productId}">Remove</button>
                `;
                cartItemsElement.appendChild(productElement);
            }
            totalAmountElement.textContent = `Total Amount : IDR ${totalAmount.toLocaleString()}`;
        }

        function calculateTotalAmount() {
            let totalAmount = 0;
            for (let productId in cart) {
                totalAmount += cart[productId].quantity * cart[productId].amount;
            }
            return totalAmount;
        }

        function goToCheckout() {
            // Calculate total amount
            let totalAmount = calculateTotalAmount();

            // Serialize cart data
            const cartData = encodeURIComponent(JSON.stringify(cart));

            // Redirect to checkout page and pass cart data and total amount as query parameters
            window.location.href = `/get-data-transactions?cart=${cartData}&totalAmount=${totalAmount}`;
        }

        // Event delegation for remove, increase, and decrease quantity buttons
        cartItemsElement.addEventListener('click', function(event) {
            const productId = event.target.getAttribute('data-id');
            if (event.target.classList.contains('cart-item-remove')) {
                removeFromCart(productId);
            } else if (event.target.classList.contains('increase-quantity')) {
                cart[productId].quantity++;
                updateCartUI();
                saveCartToLocalStorage();
            } else if (event.target.classList.contains('decrease-quantity')) {
                if (cart[productId].quantity > 1) {
                    cart[productId].quantity--;
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
