'use strict';



/**
 * navbar toggle
 */

const navbar = document.querySelector("[data-navbar]");
const navbarLinks = document.querySelectorAll("[data-nav-link]");
const menuToggleBtn = document.querySelector("[data-menu-toggle-btn]");

menuToggleBtn.addEventListener("click", function () {
  navbar.classList.toggle("active");
  this.classList.toggle("active");
});

for (let i = 0; i < navbarLinks.length; i++) {
  navbarLinks[i].addEventListener("click", function () {
    navbar.classList.toggle("active");
    menuToggleBtn.classList.toggle("active");
  });
}



/**
 * header sticky & back to top
 */

const header = document.querySelector("[data-header]");
const backTopBtn = document.querySelector("[data-back-top-btn]");

window.addEventListener("scroll", function () {
  if (window.scrollY >= 100) {
    header.classList.add("active");
    backTopBtn.classList.add("active");
  } else {
    header.classList.remove("active");
    backTopBtn.classList.remove("active");
  }
});

let cartItemCount = localStorage.getItem('cartItemCount') ? parseInt(localStorage.getItem('cartItemCount')) : 0;
document.getElementById('cart-button').innerText = `Cart Items: ${cartItemCount}`;



    function redirectToCart() {
        window.location.href = "cart.php";
    }

    function redirectToLogout() {
        localStorage.removeItem('cartItemCount');
        localStorage.removeItem('cartItems');
        window.location.href = "logout.php"; 
    }

function addToCart(itemName, itemPrice) {
let cartItems = JSON.parse(localStorage.getItem('cartItems')) || [];
let newItem = { id: new Date().getTime(), name: itemName, price: itemPrice };

cartItems.push(newItem);
localStorage.setItem('cartItems', JSON.stringify(cartItems));

function updateCartUI() {
  let cartItems = JSON.parse(localStorage.getItem('cartItems')) || [];
  let cartButton = document.getElementById('cart-button');
  if (cartButton) {
      cartButton.textContent = `Cart Items: ${cartItems.length}`;
  } else {
      console.error('Cart button element not found.');
  }
}

updateCartUI();

fetch('addToCart.php', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
    },
    body: JSON.stringify({ itemName: itemName, itemPrice: itemPrice }),
})
.then(response => response.json())
.then(data => {
    console.log('Item added to database:', data);
})
.catch(error => {
    console.error('Error adding item to database:', error);
});

let cartMessage = event.target.closest('.food-menu-card').querySelector('.cart-message');
cartMessage.innerText = "Item added to cart!";
cartMessage.style.visibility = 'visible';
setTimeout(function() {
    cartMessage.style.visibility = 'hidden';
}, 2000);
}

// for the color for every button u click it change
const filterButtons = document.querySelectorAll('.filter-btn');

function handleButtonClick(event) {
console.log('Button clicked:', event.target.textContent);
filterButtons.forEach(button => button.classList.remove('active'));
event.target.classList.add('active');
}

filterButtons.forEach(button => {
button.addEventListener('click', handleButtonClick);
});

// for the food menu
document.addEventListener('DOMContentLoaded', function () {
const filterButtons = document.querySelectorAll('.filter-btn');
const foodMenuCards = document.querySelectorAll('.food-menu-card');

foodMenuCards.forEach(card => {
card.classList.add('active');
});

filterButtons.forEach(button => {
button.addEventListener('click', function () {
const category = button.getAttribute('data-category');

filterButtons.forEach(btn => btn.classList.remove('active'));
button.classList.add('active');

foodMenuCards.forEach(card => {
if (category === 'all' || card.getAttribute('data-category') === category) {
  card.classList.add('active'); 
} else {
  card.classList.remove('active'); 
}
});
});
});
});