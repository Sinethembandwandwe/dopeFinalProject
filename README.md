# dopeFinalProject
For the CS3 final project, we created the Daily Outfit Planner website to help you pick your outfit for the day.
Live Demo: https://dope.42web.io/
Note: Some 3D features (cartoon generator, .glb model loading) do not work on the demo due to InfinityFree hosting limitations. See the screenshots below for a full demonstration of the functionality.

# Overview
DOP{e} – Daily Outfit Planner is a full‑stack web application that allows users to:
Log in and create a personal account
Generate 3D cartoon characters from customizable assets
Dress the character in different outfits
Save and view previously created outfits while logged in
Manage multiple outfits through secure backend storage

This project demonstrates both frontend and backend development, including 3D model handling, authentication, and dynamic content storage.

# Features
* User Accounts & Authentication
- Register and log in securely
- Access the full cartoon/outfit generator only when logged in
- Upload a profile picture
- Update or delete your account

* Dynamic 3D Cartoon Character (Logged‑in Users Only)
  - Full 3D cartoon character displayed using .glb models
  - Clothing items dynamically change on the character
  - Outfit preview updates in real-time

* Weather‑Based Outfit Suggestions
  - Fetches live weather using an external API key
  - Suggests appropriate outfits based on weather conditions

* Mood & Style Filters
  - Mood filter: Happy, Sad

  - Style filter: Casual, Formal, or Gothic

* Special Occasion Filter

  - Choose outfits for special events: Date night, Wedding, or Graduation

* Outfit Generator

"Generate Outfit" button selects clothes based on filters
  ~ Outfit is applied to the 3D model

"Save" button appears after generating

* Saved Outfits
  ~ View all previously saved outfits
  ~ Gallery layout connected to your account

* Fully Dynamic Website
  ~ Modern UI design
  ~Smooth interactions
  ~Mobile‑friendly and responsive
  ~Works with real backend data

# Hosting Limitations

This project is hosted on InfinityFree for demonstration purposes. InfinityFree blocks:
.glb and other 3D model file types
CORS requests for dynamic asset loading
Some XMLHttpRequest calls

As a result, the 3D cartoon character generator does not load online. Clothing models may not appear. All backend pages, the login system, and navigation load correctly

Fully working screenshots are included below to demonstrate functionality not available on the demo.

The welcome page of the cartoon shows the model rotating at 180 degrees <img width="1366" height="731" alt="image" src="https://github.com/user-attachments/assets/5e2327a1-31b3-4d06-92e4-d70912ee369e" />

Weather using the location (API Key, edit cartoon.js to insert your own key) <img width="1366" height="732" alt="image" src="https://github.com/user-attachments/assets/1fe1aa4c-07b7-4580-80ba-4418c4ee6f62" />

Generate outfit using a mood + style filter <img width="1366" height="734" alt="image" src="https://github.com/user-attachments/assets/e0981a58-e33c-48c6-9128-7b276485aef0" />

Generate outfit using special occassion filter <img width="1366" height="729" alt="image" src="https://github.com/user-attachments/assets/0a9a0fcc-13ec-4760-be40-1b79386a3f19" />

<img width="1366" height="727" alt="image" src="https://github.com/user-attachments/assets/e8fb7b6a-5ced-476f-adfb-abeccbaf341d" />

Save an outfit <img width="1366" height="725" alt="image" src="https://github.com/user-attachments/assets/e2bd8644-6ad4-446c-8861-7335542b0194" />

View outfit on your wardrobe <img width="1366" height="725" alt="image" src="https://github.com/user-attachments/assets/ca867cfb-b14b-4900-a64f-7b98416e12e8" />

Technologies Used
Frontend:
- HTML5
- CSS3
- JavaScript
- 3D rendering with .glb assets

Backend:
- PHP
- MySQL
- Sessions & Authentication

Tools:
- Git & GitHub
-InfinityFree hosting
- Local development with XAMPP

# Local Setup Instructions

To run the project with full functionality:

- Clone this repository:git clone https://github.com/Sinethembandwandwe/YOUR_REPO.git
- Create a config.php file based on the file DbConnect.php:
<?php
$host = "YOUR_HOST";
$user = "YOUR_USERNAME";
$password = "YOUR_PASSWORD";
$database = "YOUR_DATABASE";
?>
- Import the provided SQL file into your MySQL database.
- Place the project folder inside your htdocs (XAMPP) directory.
- Start Apache + MySQL.
- All 3D models and outfit generator features will now load correctly.

# Credits

This project was developed as part of a student web development module (team members included on the website). All 3D models and assets were created or assembled specifically for this project.

# Contact

For questions or collaboration: Developer: Sinethemba Ndwandwe

!!!!!!! Thank you for viewing this project! !!!!!!!!!!






