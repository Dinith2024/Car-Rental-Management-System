<?php
// error_reporting(0); // You can enable this for debugging
include('includes/config.php');

// PHP logic for form submission
if (isset($_POST['submit_rental'])) {
    // Collect and sanitize form data
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $car_model = trim($_POST['car_model']);
    $pickup_date = $_POST['pickup_date'];
    $return_date = $_POST['return_date'];
    $notes = trim($_POST['notes']);

    // Basic validation
    if (empty($full_name) || empty($email) || empty($phone) || empty($car_model) || empty($pickup_date) || empty($return_date)) {
        echo "<script>alert('Please fill out all required fields.');</script>";
        echo "<script>window.location.href='rental-plans.php';</script>";
        exit;
    }
    
    // Convert to DateTime objects for comparison
    $pickup_datetime = new DateTime($pickup_date);
    $return_datetime = new DateTime($return_date);

    // Check if return date is after pickup date
    if ($return_datetime <= $pickup_datetime) {
        echo "<script>alert('Return date must be after pickup date.');</script>";
        echo "<script>window.location.href='rental-plans.php';</script>";
        exit;
    }

    // SQL query to insert data into the rentals table
    $sql = "INSERT INTO rentals (full_name, email, phone, car_model, pickup_date, return_date, notes) VALUES (:full_name, :email, :phone, :car_model, :pickup_date, :return_date, :notes)";
    $query = $dbh->prepare($sql);
    
    // Bind parameters
    $query->bindParam(':full_name', $full_name, PDO::PARAM_STR);
    $query->bindParam(':email', $email, PDO::PARAM_STR);
    $query->bindParam(':phone', $phone, PDO::PARAM_STR);
    $query->bindParam(':car_model', $car_model, PDO::PARAM_STR);
    $query->bindParam(':pickup_date', $pickup_date, PDO::PARAM_STR);
    $query->bindParam(':return_date', $return_date, PDO::PARAM_STR);
    $query->bindParam(':notes', $notes, PDO::PARAM_STR);

    try {
        $query->execute();
        $lastInsertId = $dbh->lastInsertId();
        if ($lastInsertId) {
            echo "<script>alert('Your rental request has been submitted successfully!');</script>";
            echo "<script>window.location.href='rental-plans.php';</script>";
        } else {
            echo "<script>alert('Something went wrong. Please try again.');</script>";
            echo "<script>window.location.href='rental-plans.php';</script>";
        }
    } catch (PDOException $e) {
        // Log the error
        // file_put_contents('PDOErrors.log', $e->getMessage(), FILE_APPEND);
        echo "<script>alert('An error occurred. Please try again later.');</script>";
        echo "<script>window.location.href='rental-plans.php';</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Most Popular Car Rental Deals</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <link href="img/favicon.ico" rel="icon">

    <link href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;500;600;700;800;900&display=swap" rel="stylesheet"> 
    
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="lib/flaticon/font/flaticon.css" rel="stylesheet">
    <link href="lib/animate/animate.min.css" rel="stylesheet">
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">

    <link href="css/style.css" rel="stylesheet">

    <style>
        .car-section {
            padding: 2rem;
            max-width: 1100px;
            margin: auto;
        }

        .sub-text {
            text-align: center;
            color: #4a5568;
            margin-bottom: 2rem;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
        }

        .tabs {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 0.5rem;
            margin-bottom: 2rem;
        }

        .tab {
            background-color: #f7fafc;
            color: #4a5568;
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 9999px; /* This makes it pill-shaped */
            border: 1px solid #e2e8f0;
            cursor: pointer;
            transition: all 0.3s ease-in-out;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }

        .tab:hover {
            background-color: #e2e8f0;
        }

        .tab.active {
            background-color: #4a5568;
            color: white;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .car-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .car-card {
            background-color: white;
            padding: 1.5rem;
            border-radius: 1.5rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: box-shadow 0.3s ease-in-out;
        }

        .car-card:hover {
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        .car-card .image-container {
            text-align: center;
        }

        .car-card img {
            width: 100%;
            height: auto;
            border-radius: 0.75rem;
            margin-bottom: 1rem;
            object-fit: cover;
        }

        .car-card .star-rating {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
        }

        .car-card .star-rating i {
            color: #fcd34d;
        }

        .car-card .star-rating span {
            margin-left: 0.5rem;
            color: #6b7280;
        }

        .car-card h3 {
            text-align: center;
            font-size: 1.125rem;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 1rem;
        }

        .car-card .specs {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .car-card .spec-item {
            display: flex;
            align-items: center;
            color: #6b7280;
        }

        .car-card .spec-item i {
            margin-right: 0.5rem;
        }
        
        .car-card .bottom-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: auto;
            border-top: 1px solid #e5e7eb;
            padding-top: 1rem;
        }

        /* Updated button styling */
        .car-card .rent-button {
            display: flex;
            align-items: center;
            background-color: #2563eb;
            color: white;
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            border: 1px solid #2563eb;
            transition: all 0.3s ease-in-out;
            cursor: pointer;
        }

        .car-card .rent-button:hover {
            background-color: #1e40af;
            border-color: #1e40af;
        }

        .car-card .rent-button i {
            margin-left: 0.5rem;
        }

        .section__header {
            margin-bottom: 1rem;
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--text-dark);
            line-height: 3.25rem;
            text-align: center;
        }
    </style>
</head>

<body>
<?php include_once('includes/header.php');?>

<div class="page-header">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h2>Most popular Car Rental Brands</h2>
            </div>
            <div class="col-12">
                <a href="index.php">Home</a>
                <a href="rental-plans.php">Price</a>
            </div>
        </div>
    </div>
</div>
<section class="car-section">
  <h2 class="section__header">Most popular car rental deals</h2>
    <p class="sub-text">
        Explore our top car rental deals, handpicked to give you the best value and experience. 
        Book now and drive your favorite ride at an incredible rate!
    </p>

    <div class="tabs">
        <button class="tab active" data-brand="tesla">Tesla</button>
        <button class="tab" data-brand="mitsubishi">Mitsubishi</button>
        <button class="tab" data-brand="mazda">Mazda</button>
        <button class="tab" data-brand="toyota">Toyota</button>
        <button class="tab" data-brand="honda">Honda</button>
    </div>

    <div class="car-list" id="car-list">
        </div>
</section>

<div class="modal fade" id="rentalModal" tabindex="-1" role="dialog" aria-labelledby="rentalModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="rentalModalLabel">Rent <span id="car-title"></span></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="rentalForm" method="POST">
          <input type="hidden" name="car_model" id="car-model-input">
          
          <div class="form-group">
            <label for="full-name">Full Name</label>
            <input type="text" class="form-control" id="full-name" name="full_name" required>
          </div>
          <div class="form-group">
            <label for="email">Email address</label>
            <input type="email" class="form-control" id="email" name="email" required>
          </div>
          <div class="form-group">
            <label for="phone">Phone Number</label>
            <input type="text" class="form-control" id="phone" name="phone" required>
          </div>
          <div class="form-group">
            <label for="pickup-date">Pickup Date</label>
            <input type="datetime-local" class="form-control" id="pickup-date" name="pickup_date" required>
          </div>
          <div class="form-group">
            <label for="return-date">Return Date</label>
            <input type="datetime-local" class="form-control" id="return-date" name="return_date" required>
          </div>
          <div class="form-group">
            <label for="notes">Additional Notes</label>
            <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
          </div>
          <button type="submit" name="submit_rental" class="btn btn-primary w-100 mt-3">Submit Rental</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.bundle.min.js"></script>
<script src="lib/easing/easing.min.js"></script>
<script src="lib/owlcarousel/owl.carousel.min.js"></script>
<script src="lib/waypoints/waypoints.min.js"></script>
<script src="lib/counterup/counterup.min.js"></script>
<script>
    // --- Data for the cars ---
    const carData = {
        tesla: [
            {
                model: "Tesla Model S",
                rating: 5,
                image: "brands/deals-1.png",
                specs: ["4 People", "Autopilot", "400km", "Electric"],
                price: 7000,
            },
            {
                model: "Tesla Model E",
                rating: 4.5,
                image: "brands/deals-2.png",
                specs: ["4 People", "Autopilot", "400km", "Electric"],
                price: 8500,
            },
            {
                model: "Tesla Model Y",
                rating: 5,
                image: "brands/deals-3.png",
                specs: ["4 People", "Autopilot", "400km", "Electric"],
                price: 9000,
            },
        ],
        mitsubishi: [
            {
                model: "Mitsubishi Outlander",
                rating: 4.8,
                image: "brands/deals-4.png",
                specs: ["5 People", "4WD", "550km", "Petrol"],
                price: 6000,
            },
            {
                model: "Mitsubishi Eclipse Cross",
                rating: 4.2,
                image: "brands/deals-5.png",
                specs: ["5 People", "2WD", "600km", "Petrol"],
                price: 6500,
            },
            {
                model: "Mitsubishi Mirage",
                rating: 3.9,
                image: "brands/deals-6.png",
                specs: ["4 People", "2WD", "700km", "Petrol"],
                price: 7000,
            },
        ],
        mazda: [
            {
                model: "Mazda CX-5",
                rating: 5,
                image: "brands/deals-7.png",
                specs: ["5 People", "AWD", "500km", "Petrol"],
                price: 9000,
            },
            {
                model: "Mazda 3",
                rating: 4.5,
                image: "brands/deals-8.png",
                specs: ["5 People", "2WD", "620km", "Petrol"],
                price: 9500,
            },
            {
                model: "Mazda MX-5",
                rating: 4.9,
                image: "brands/deals-9.png",
                specs: ["2 People", "RWD", "450km", "Petrol"],
                price: 10000,
            },
        ],
        toyota: [
            {
                model: "Toyota RAV4",
                rating: 5.3,
                image: "brands/deals-10.png",
                specs: ["5 People", "Hybrid", "650km", "Hybrid"],
                price: 11000,
            },
            {
                model: "Toyota Camry",
                rating: 5,
                image: "brands/deals-11.png",
                specs: ["5 People", "2WD", "700km", "Petrol"],
                price: 12000,
            },
            {
                model: "Toyota Highlander",
                rating: 5.2,
                image: "brands/deals-12.png",
                specs: ["7 People", "AWD", "600km", "Petrol"],
                price: 20000,
            },
        ],
        honda: [
            {
                model: "Honda Civic",
                rating: 4.9,
                image: "brands/deals-13.png",
                specs: ["5 People", "2WD", "580km", "Petrol"],
                price: 5000,
            },
            {
                model: "Honda CR-V",
                rating: 5,
                image: "brands/deals-14.png",
                specs: ["5 People", "AWD", "550km", "Petrol"],
                price: 6000,
            },
            {
                model: "Honda Accord",
                rating: 5,
                image: "brands/deals-15.png",
                specs: ["5 People", "2WD", "610km", "Petrol"],
                price: 8000,
            },
        ]
    };

    const carListElement = document.getElementById('car-list');
    const tabButtons = document.querySelectorAll('.tab');

    /**
     * Generates the HTML for a single car card.
     * @param {Object} car - The car data object.
     * @returns {string} The HTML string for the car card.
     */
    function createCarCard(car) {
        // Function to generate the star rating
        function generateStars(rating) {
            let starsHtml = '';
            const fullStars = Math.floor(rating);
            for (let i = 0; i < 5; i++) {
                if (i < fullStars) {
                    starsHtml += '<i class="fas fa-star"></i>';
                } else if (i < rating) {
                    starsHtml += '<i class="fas fa-star-half-alt"></i>';
                } else {
                    starsHtml += '<i class="far fa-star"></i>';
                }
            }
            return starsHtml;
        }

        // Function to generate the spec icons and text
        function generateSpecs(specs) {
            const icons = {
                "People": "fa-users",
                "Autopilot": "fa-location-crosshairs",
                "4WD": "fa-gear",
                "2WD": "fa-gear",
                "km": "fa-gauge-high",
                "Electric": "fa-charging-station",
                "Petrol": "fa-gas-pump",
                "Hybrid": "fa-plug",
                "RWD": "fa-gears"
            };
            return specs.map(spec => `
                <div class="spec-item">
                    <i class="fas ${icons[spec.split(' ')[1] || spec]}"></i>
                    <span>${spec}</span>
                </div>
            `).join('');
        }

        return `
            <div class="car-card">
                <div class="image-container">
                    <img src="${car.image}" alt="${car.model}" onerror="this.onerror=null;this.src='https://placehold.co/600x400/1f2937/ffffff?text=Image+Not+Found';">
                </div>
                <div>
                    <div class="star-rating">
                        ${generateStars(car.rating)}
                        <span>(${car.rating})</span>
                    </div>
                    <h3>${car.model}</h3>
                    <div class="specs">
                        ${generateSpecs(car.specs)}
                    </div>
                </div>
                <div class="bottom-row">
                    <div>
                        <span class="price">LKR ${car.price}</span>
                        <span>/Per Day</span>
                    </div>
                    <button class="rent-button" data-toggle="modal" data-target="#rentalModal" data-car-model="${car.model}">
                        <span>Rent Now</span>
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </div>
        `;
    }

    /**
     * Renders the car cards for a given brand.
     * @param {string} brand - The name of the car brand.
     */
    function renderCars(brand) {
        const cars = carData[brand];
        carListElement.innerHTML = ''; // Clear previous cards
        if (cars) {
            cars.forEach(car => {
                carListElement.innerHTML += createCarCard(car);
            });
        } else {
            carListElement.innerHTML = '<p style="text-align: center; color: #6b7280; grid-column: span 3;">No cars available for this brand.</p>';
        }
    }

    // --- Event Listeners ---
    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            // Remove 'active' class from all buttons
            tabButtons.forEach(btn => btn.classList.remove('active'));

            // Add 'active' class to the clicked button
            button.classList.add('active');

            // Get the brand from the data-brand attribute and render the cars
            const brand = button.dataset.brand;
            renderCars(brand);
        });
    });

    // Update the modal with car information when it's about to be shown
    $('#rentalModal').on('show.bs.modal', function (event) {
        const button = $(event.relatedTarget); // Button that triggered the modal
        const carModel = button.data('car-model'); // Extract info from data-* attributes
        
        const modal = $(this);
        modal.find('#car-title').text(carModel);
        modal.find('#car-model-input').val(carModel);
    });

    // Initial render on page load with the Tesla cars
    window.onload = () => {
        renderCars('tesla');
    };
</script>
</body>
</html>