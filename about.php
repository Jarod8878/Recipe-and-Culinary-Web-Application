<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>About Us</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <link rel="stylesheet" href="styling.css">
    <style>
        .members-section {
            margin-top: 40px;
            padding: 30px 10px;
            border-top: 2px dashed #ccc;
        }

        .profile-pic {
            text-align: center;
        }

        .profile-pic img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid #fff;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
            background-color: white;
            transition: transform 0.3s ease-in-out;
        }

        .profile-pic img:hover {
            transform: scale(1.05);
        }

        .profile-pic p {
            margin-top: 12px;
            font-weight: bold;
            color: #333;
        }

        .card {
            background-color: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(4px);
        }

        .card h2,
        .card h4 {
            font-size: 1.75rem;
            font-weight: 700;
            color: #2c3e50;
            text-align: center;
        }

        .card ul li {
            font-size: 1.05rem;
            line-height: 1.8;
            text-align: center;
        }


        .card p {
            font-size: 1.05rem;
            text-align: center;
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark px-3">
        <a class="navbar-brand fw-bold text-white" href="#">Culinary App</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link active" href="about.php">About Us</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="login.php">Back to Login</a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="card">
            <h2 class="mb-4">About This Recipe & Culinary Web Application</h2>
            <p>
                This web application is designed to allow users to upload and rate recipes, plan meals, engage in
                discussions, and compete by sharing their best recipes.
            </p>

            <div class="text-center">
                <h4 class="fw-bold">Key Features</h4>
                <ul class="list-unstyled d-inline-block text-start">
                    <li>User-friendly recipe sharing system</li>
                    <li>Meal planning functionality</li>
                    <li>Discussion forums for user interaction</li>
                    <li>Competition module with recipe rankings</li>
                </ul>
            </div>

            <div class="members-section">
                <h4 class="mb-4">Our Members</h4>
                <div class="row justify-content-center g-4">
                    <div class="col-6 col-md-3">
                        <div class="profile-pic">
                            <img src="uploads/profile(male).png" alt="Lee Jia Wei">
                            <p>Lee Jia Wei</p>
                        </div>
                    </div>

                    <div class="col-6 col-md-3">
                        <div class="profile-pic">
                            <img src="uploads/profile(male).png" alt="Jarod Lim">
                            <p>Jarod Lim</p>
                        </div>
                    </div>

                    <div class="col-6 col-md-3">
                        <div class="profile-pic">
                            <img src="uploads/profile(female).png" alt="Sharon Liew">
                            <p>Sharon Liew</p>
                        </div>
                    </div>

                    <div class="col-6 col-md-3">
                        <div class="profile-pic">
                            <img src="uploads/profile(female).png" alt="Wong Poh Ern">
                            <p>Wong Poh Ern</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>