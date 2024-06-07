<?php
session_start();
if (!isset($_SESSION["user"])) {
   header("Location: login.php");
}
include('database.php');
$query = "SELECT * FROM products";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jerry's Pares and Bulalohan</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&family=Rubik:wght@400;500;600;700&family=Shadows+Into+Light&display=swap" rel="stylesheet">
    <link rel="preload" as="image" href="./images/hero-banner.png" media="min-width(768px)">
    <link rel="preload" as="image" href="./images/hero-banner-bg.png" media="min-width(768px)">
    <link rel="preload" as="image" href="./images/hero-bg.jpg">
    <link rel="icon" href="/images/jerrys-logos.png">
</head>
<body id="top">
    
<style>
    .cart-message {
        visibility: hidden;
        font-weight: 700;
        color: white;
        background-color: var(--dark-orange);
        border-radius: 10px;
        margin: 10px;
        padding: 5px;
    }

    .selectButtons {
      background-color: var(--dark-orange);
      width: auto;
      height: 30px;
      border-radius: 5px;
      margin: 10px;
      padding: 10px;
      padding-top: 3px;
    }

    .selectButtons:hover {
      background-color: orange;
    }

    .food-menu-card {
      display:none; 
    }
    .food-menu-card.active {
      display: block;
    }
</style>

<header class="header active" data-header>
  <div class="container">

    <h1>
      <a href="#" class="logo">Jerry's Pares and Bulalohan<span class="span">.</span></a>
    </h1>

    <nav class="navbar" data-navbar>
      <ul class="navbar-list">

        <li class="nav-item">
          <a href="#home" class="navbar-link" data-nav-link>Home</a>
        </li>

        <li class="nav-item">
          <a href="#food-menu" class="navbar-link" data-nav-link>Menu</a>
        </li>

        <li class="nav-item">
          <a href="#aboutus" class="navbar-link" data-nav-link>About us</a>
        </li>

        <li class="nav-item">
          <a href="#contactus" class="navbar-link" data-nav-link>Contact Us</a>
        </li>

        <li class="nav-item">
          <a href="order.php" class="navbar-link" data-nav-link>Orders</a>
        </li>

      </ul>
    </nav>

    <div class="header-btn-group">
    <button id="cart-button" class="btn btn-hover" onclick="redirectToCart()">Cart Items: 0</button>

    <button class="btn btn-hover" onclick="redirectToLogout()">Logout</button>

      <script>

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
        updateCartCount();
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

function updateCartCount() {
    fetch('getCartCount.php')
    .then(response => response.json())
    .then(data => {
        let cartButton = document.getElementById('cart-button');
        if (cartButton) {
            cartButton.textContent = `Cart Items: ${data.cart_count}`;
        } else {
            console.error('Cart button element not found.');
        }
    })
    .catch(error => {
        console.error('Error fetching cart count:', error);
    });
}

document.addEventListener('DOMContentLoaded', function () {
    updateCartCount();
});

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
        if (category === 'All' || card.getAttribute('data-category') === category) {
          card.classList.add('active'); 
        } else {
          card.classList.remove('active'); 
        }
      });
    });
    });
    });
</script>

      <button class="nav-toggle-btn" aria-label="Toggle Menu" data-menu-toggle-btn>
        <span class="line top"></span>
        <span class="line middle"></span>
        <span class="line bottom"></span>
      </button>
    </div>
  </div>
</header>
    
    <main> 
        <article>
            <!--   First Section   -->
            <section class="hero" id="home" style="background-image: url('images/hero-bg.jpg')">
                <div class="container">
        
                  <div class="hero-content">
        
                    <p class="hero-subtitle">Eat Sleep And Repeat</p>
        
                    <h2 class="h1 hero-title">Jerry's "The Original Pares"</h2>
        
                    <p class="hero-text">Beef pares is a Filipino raised beef stew with garlic fried rice, and a bowl of clear soup. It is a popular food particularly associated with specialty roadside diner-style establishments known as paresan</p>
        
                    <button class="btn" onclick="scrollToFoodMenu()">Add to Cart</button>
        
                  </div>
        
                  <figure class="hero-banner">
                    <img src="./images/hero-banner-bg.png" width="820" height="716" alt="" aria-hidden="true"
                      class="w-100 hero-img-bg">
        
                    <img src="./images/hero-banner.png  " width="700" height="637" loading="lazy" alt="pares"
                      class="w-100 hero-img">
                  </figure>
        
                </div>
              </section>

            <!--   Second Section  -->
            <section class="section section-divider white promo">
                <div class="container">

                    <!--  first picture   -->

                    <ul class="promo-list has-scrollbar">
          
                      <li class="promo-item">
                        <div class="promo-card">

                 <h3 class="h3 card-title">Bulalo</h3>            
                <p class="card-text">
                    Savor the succulent goodness of our Bulalo, tender beef shanks, simmered to perfection in a rich, savory broth. A hearty delight that promises to satisfy your cravings and keep you coming back for more!
                </p>

                <img src="./images/promo-1.png" width="300" height="300" loading="lazy" alt="bulalo"
                  class="w-100 card-banner">
              </div>
              </li>

              <!--    second picture    -->

              <li class="promo-item">
                <div class="promo-card">
  
                  <h3 class="h3 card-title">Menudo</h3>
                  <p class="card-text">
                    Discover the irresistible flavors of our Menudo, A hearty blend of tender pork, potatoes, carrots, and bell peppers, simmered in a savory tomato sauce
                  </p>
  
                  <img src="./images/promo-2.png" width="300" height="300" loading="lazy" alt="Soft Drinks"
                    class="w-100 card-banner">
                </div>
              </li>

              <!--    third picture    -->

              <li class="promo-item">
                <div class="promo-card">
  
                  <h3 class="h3 card-title">Chicken Curry</h3>
                  <p class="card-text">
                    Experience the exotic allure of our Chicken Curry, Tender chicken pieces cooked in a fragrant blend of spices and creamy coconut milk. A flavorful journey that promises to spice up your dining experience.
                  </p>
  
                  <img src="./images/promo-3.png" width="300" height="300" loading="lazy" alt="French Fry"
                    class="w-100 card-banner">
                </div>
              </li>
                
              <!--    fourth picture    -->

                    <li class="promo-item">
                        <div class="promo-card">
          
                          <h3 class="h3 card-title">Dinakdakan</h3>
          
                          <p class="card-text">
                            Indulge in the bold flavors of our Dinakdakan, Grilled pork cheeks and liver, mixed with onions, chili peppers, and calamansi juice. A mouthwatering Filipino delicacy that packs a punch and will keep you coming back for another bite.
                          </p>

                          <img src="./images/promo-4.png" width="300" height="300" loading="lazy" alt="Chicken Masala"
                            class="w-100 card-banner">
                        </div>
                      </li>
                </div>
              </li>
            </ul>
            </section>

            <!--   Third Section   -->
            <section class="section food-menu" id="food-menu">
                <br> 
                <div class="container">
                    <p class="section-subtitle">Jerry's Dishes</p>

          <h2 class="h2 section-title">
            Our Delicious <span class="span">Meals</span>
          </h2>

          <p class="section-text">
            Crafted with care and passion in our local kitchen, using only the freshest ingredients.
          </p>

          <ul class="fiter-list">
            <li>
              <button class="filter-btn  active" data-category="All">All</button>
            </li>
            <li>
              <button class="filter-btn" data-category="Dishes" >Dishes</button>
            </li>
            <li>
              <button class="filter-btn" data-category="Add-ons">Add-ons</button>
            </li>
            <li>
              <button class="filter-btn" data-category="Drinks">Drinks</button>
            </li>
          </ul>

          <ul class="food-menu-list">
        <?php
        if (mysqli_num_rows($result) > 0) {
            while ($product = mysqli_fetch_assoc($result)) {
                ?>
                <li>
                    <div class="food-menu-card" data-category="<?php echo htmlspecialchars($product['category']); ?>">
                        <div class="card-banner">
                        <img src="../admin-panel-2/images/<?php echo htmlspecialchars($product['image']); ?>" width="300" height="300" loading="lazy" 
                        alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-100">
                            <button class="btn food-menu-btn" onclick="addToCart('<?php echo htmlspecialchars($product['name']); ?>', <?php echo htmlspecialchars($product['price']); ?>)">Add to Cart</button>
                        </div>
                        <div class="wrapper">
                            <div class="rating-wrapper">
                                <ion-icon name="star"></ion-icon>
                                <ion-icon name="star"></ion-icon>
                                <ion-icon name="star"></ion-icon>
                                <ion-icon name="star"></ion-icon>
                                <ion-icon name="star"></ion-icon>
                            </div>
                        </div>
                        <h3 class="h3 card-title"><?php echo htmlspecialchars($product['name']); ?></h3>
                        <div class="price-wrapper">
                            <p class="price-text">Price:</p>
                            <data class="price" value="<?php echo htmlspecialchars($product['price']); ?>">P<?php echo htmlspecialchars(number_format($product['price'], 2)); ?></data>
                            <div class="cart-message">
                                <p>Item added to Cart</p>
                            </div>
                        </div>
                    </div>
                </li>
                <?php
            }
        } else {
            echo "<p>No products found.</p>";
        }
        ?>
    </ul>

        </div>
      </section>

      <section class="hero" id="aboutus" style="background-image: url('images/hero-bg.jpg')">
        
        <div class="container">

          <div class="hero-content">

            <p class="hero-subtitle">ABOUT US</p>

            <h2 class="h1 hero-title">Jerry's Pares and Bulalohan</h2>

            <p class="hero-text">Jerry's Pares is a small, no-frills eatery in Makati. We have a wide range of dishes to offer. From its bestseller foods: Pares, Bulalo and Dinakdakan to the usual silogs, fried chicken and chicharon can be found here.</p>     

          </div>

          <figure class="hero-banner">
            <img src="./images/hero-banner-bg.png" width="820" height="716" alt="" aria-hidden="true"
              class="w-100 hero-img-bg">
          </figure>

        </div>

      </section>

    <section id="contactus" class="contact-section">
        <h2 class="h3 card-title">Contact Us</h2>
        <address class="h3 card-title">
          <p >Visit Us: 131A 13th Avenue, East Rembo, Makati, Philippines</p>
          <p>Call Us: 0967 337 6890</p>
          <p>Email Us: jfagotoandmamihaus@gmail.com</p>
          <ul class="social-icons">
            <li><p>Our Facebook Page <a href="https://www.facebook.com/ILoveJerrysPares" target="_blank"><i class="fab fa-facebook"></i></a></p></li>
          </ul>
        </address>
      </section>      
      
        </article>

    </main>

    <script >

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
    </script>

    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
</body>
</html>