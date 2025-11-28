// Global Slideshow Variables and Functions
// These are defined globally so HTML buttons (e.g., onclick) can access them directly,

let slideIndex = 1;

/**
 * Controls the visibility of slides in the gallery.
 * @param {number} n - The direction/value to change the slide index.
 */
function showSlides(n) {
    const slides = document.getElementsByClassName("mySlides");

    if (slides.length === 0) return; // Exit if no slides are found

    // Wrap around logic: if index is past the last slide, go to the first.
    if (n > slides.length) slideIndex = 1; 
    // Wrap around logic: if index is before the first slide, go to the last.
    if (n < 1) slideIndex = slides.length; 

    // Hide all slides
    for (let i = 0; i < slides.length; i++) {
        slides[i].style.display = "none";
    }

    // Show the current slide
    slides[slideIndex - 1].style.display = "block";
}

/**
 * Advances or retreats the slide index.
 * @param {number} n - 1 for next slide, -1 for previous slide.
 */
function plusSlides(n) {
    showSlides(slideIndex += n);
}
// --------------------------------------------------


// Wait for the full HTML content to load before running any remaining scripts
document.addEventListener("DOMContentLoaded", () => {
    
    // Initialize slideshow after the DOM is loaded
    showSlides(slideIndex); 

    /*** ---- PART 1: DOM Manipulations & Style Improvements ---- ***/

    // 1. Change Home section heading text
    const homeHeading = document.querySelector("#home h2");
    if (homeHeading) {
        homeHeading.textContent = "Welcome to DOP{e} - Your Daily Fit Helper";
        homeHeading.style.color = "blueviolet";
        homeHeading.style.fontSize = "2rem";
    }

    // 2. Add a new paragraph dynamically
    const homeSection = document.getElementById("home");
    if (homeSection) {
        const newPara = document.createElement("p");
        newPara.textContent = "Plan your vibe, match your style, and rule the day!";
        homeSection.appendChild(newPara);
    }

    // 3. Replace the dashed HR with solid one
    const hr = document.querySelector("#home hr");
    if (hr) {
        hr.setAttribute("style", "border: 2px solid hotpink;");
    }

    // 4. Update Everyday list items
    const everydayList = document.querySelector(".everyday-list");
    if (everydayList && everydayList.firstElementChild) {
        everydayList.firstElementChild.innerHTML = "Earthy: linen shirt, sandals";
    }

    // 5. Add a new Everyday style dynamically
    if (everydayList) {
        const newStyle = document.createElement("li");
        newStyle.textContent = "Streetwear: oversized hoodie, sneakers";
        everydayList.appendChild(newStyle);
    }

    
    // Apply multi-column layout only to the list for better readability.
    
    if (everydayList) {
        // Set the list to display in two columns with a dashed rule for visual separation.
        everydayList.style.columnCount = 2;
        everydayList.style.columnRule = "1px dashed lightgray";
        everydayList.style.columnGap = "20px";
        everydayList.style.marginBottom = "30px"; // Add separation from next section (tables)
        console.log("Multi-column layout applied to the everyday list.");
    }

    // Applying multi-column layout to the form container for better structure and separation.
    const formsContainer = document.getElementById("auth-forms-container");
    if (formsContainer) {
        formsContainer.style.columnCount = 2;
        formsContainer.style.columnGap = "40px";
        formsContainer.style.padding = "20px"; // Add separation inside the container
        formsContainer.style.border = "1px solid #ffb6c1"; // Add subtle border for separation
        formsContainer.style.borderRadius = "8px";
        formsContainer.style.marginTop = "30px"; // Add separation from other content
        formsContainer.style.marginBottom = "30px";
        console.log("Multi-column layout applied to the authentication forms container.");
    }
   

    // 6. Change table background color
    const everydayTable = document.getElementById("everyday-table");
    if (everydayTable) {
        everydayTable.style.backgroundColor = "#ffe4e1";
        // Ensure tables have full width and sufficient margin for separation
        everydayTable.style.width = "100%";
        everydayTable.style.marginBottom = "30px";
    }

    // 7. Insert a new row in the everyday table
    if (everydayTable) {
        const newRow = everydayTable.insertRow();
        newRow.innerHTML = "<td>Cap</td><td>Accessory</td><td>Black</td>";
    }

    // 8. Hide and then show the "Occasion Outfit Items" table
    const occasionTable = document.getElementById("occasion-table");
    if (occasionTable) {
        occasionTable.style.display = "none";
        setTimeout(() => {
            occasionTable.style.display = "table";
        }, 2000);
        // Ensure this table also has full width for display
        occasionTable.style.width = "100%";
    }

    // 9. Show browser details using document properties 
    const browserDiv = document.getElementById("browser-details");
    if (browserDiv) {
        browserDiv.innerHTML = `
            <p><strong>Browser Info (Document Properties):</strong></p>
            <ul>
                <li>Title: ${document.title}</li>
                <li>URL: ${document.URL}</li>
                <li>Last Modified: ${document.lastModified}</li>
                <li>Domain: ${document.domain}</li>
            </ul>
        `;
    }

    // 10. Change footer text style
    const footerParas = document.querySelectorAll("footer p");
    footerParas.forEach(p => (p.style.fontWeight = "bold"));

    // 11. Log number of forms
    console.log("Number of forms on page:", document.forms.length);


    /*** ---- PART 2: DOM Events + Functions ---- ***/

    // 1. Click event (home button) => Change heading text
    function changeHeading() {
        if (homeHeading) {
            homeHeading.textContent = "👗 Outfit Planner – Slay the Day!";
            homeHeading.style.color = "darkmagenta";
        }
    }
    const homeBtn = document.getElementById("homeBtn");
    if (homeBtn) {
        homeBtn.addEventListener("click", changeHeading);
    }

    // 2. Mouseover event => Highlight home paragraph
    const homePara = document.querySelector("#home p");
    function highlightHome() {
        if (homePara) homePara.style.backgroundColor = "#ffe4e1";
    }
    if (homePara) {
        homePara.addEventListener("mouseover", highlightHome);
    }

    // 3. Mouseout event = Remove highlight
    function removeHighlight() {
        if (homePara) homePara.style.backgroundColor = "transparent";
    }
    if (homePara) {
        homePara.addEventListener("mouseout", removeHighlight);
    }

    // 4. Submit event (style form) 
    const styleForm = document.getElementById("style-form");
    function handleStyleSubmit(e) {
        e.preventDefault();
        const styleChoice = document.getElementById("style").value;
        // Using console.log instead of alert for better compatibility
        console.log("Style form submitted:", styleChoice); 
    }
    if (styleForm) {
        styleForm.addEventListener("submit", handleStyleSubmit);
    }

    // 5. Submit event (mood form) => Display mood on page
    const moodForm = document.getElementById("mood-form");
    function handleMoodSubmit(e) {
        e.preventDefault();
        const mood = document.getElementById("mood").value;
        if (homeSection) {
            const para = document.createElement("p");
            para.textContent = "Mood saved: " + mood;
            homeSection.appendChild(para);
        }
    }
    if (moodForm) {
        moodForm.addEventListener("submit", handleMoodSubmit);
    }

    // 6. Double-click event => Add new outfit suggestion
    function addOutfitSuggestion() {
        if (everydayList) {
            const newItem = document.createElement("li");
            newItem.textContent = "Surprise Fit: Hoodie + Jeans + Sneakers";
            everydayList.appendChild(newItem);
        }
    }
    if (everydayList) {
        everydayList.addEventListener("dblclick", addOutfitSuggestion);
    }

    // 7. Focus event => Highlight mood input
    const moodInput = document.getElementById("mood");
    function focusMoodInput() {
        if (moodInput) moodInput.style.border = "2px solid hotpink";
    }
    if (moodInput) {
        moodInput.addEventListener("focus", focusMoodInput);
    }

    // 8. Blur event => Remove highlight
    function blurMoodInput() {
        if (moodInput) moodInput.style.border = "1px solid grey";
    }
    if (moodInput) {
        moodInput.addEventListener("blur", blurMoodInput);
    }

    // 9. Keydown event => Detect typing in mood box
    function detectTyping(e) {
        console.log("Key pressed:", e.key);
    }
    if (moodInput) {
        moodInput.addEventListener("keydown", detectTyping);
    }

    // 10. Change event => Log style selection
    const styleSelect = document.getElementById("style");
    function styleChanged() {
        alert("Style changed to:", styleSelect.value);
    }
    if (styleSelect) {
        styleSelect.addEventListener("change", styleChanged);
    }

    // 11. Load event => Show welcome message (NOTE: load fires once for the entire window)
    /*function pageLoaded() {
        alert("Welcome to DOP{e}! Ready to plan your outfit?");
    }
    window.addEventListener("load", pageLoaded);*/

    // 12. Scroll event => Change footer color
    function changeFooterOnScroll() {
        document.querySelector("footer").style.backgroundColor = "#f8bbd0";
    }
    window.addEventListener("scroll", changeFooterOnScroll);

    /*** ---- EXTRA: Table Interactions ---- ***/

    // 13. Click event on table cell => highlight it
    function highlightCell(e) {
        if (e.target.tagName === "TD") {
            e.target.style.backgroundColor = "#ff00c8ff";
        }
    }
    if (everydayTable) {
        everydayTable.addEventListener("click", highlightCell);
    }

    // 14. Mouseover event on table headers => change text color
    function headerHover(e) {
        if (e.target.tagName === "TH") {
            e.target.style.color = "crimson";
        }
    }
    if (everydayTable) {
        everydayTable.addEventListener("mouseover", headerHover);
    }

    // 15. Mouseout event on table headers => revert text color
    function headerUnhover(e) {
        if (e.target.tagName === "TH") {
            e.target.style.color = "black";
        }
    }
    if (everydayTable) {
        everydayTable.addEventListener("mouseout", headerUnhover);
    }

    // 16. Navigator Browser Info 
    const browserInfoLink = document.getElementById("Browser-info");
    const infoDiv = document.getElementById("browser-details");

    if (browserInfoLink && infoDiv) {
        browserInfoLink.addEventListener("click", function (event) {
            event.preventDefault();

            // Use 5+ Navigator Object Properties
            const appVersion = navigator.appVersion;
            const language = navigator.language;
            const appName = navigator.appName;
            const appCodeName = navigator.appCodeName;
            const userAgent = navigator.userAgent;

            infoDiv.innerHTML = `
                <h3>Your Browser Info (Navigator Properties)</h3>
                <ul>
                    <li><strong>App Name:</strong> ${appName}</li>
                    <li><strong>Code Name:</strong> ${appCodeName}</li>
                    <li><strong>App Version:</strong> ${appVersion}</li>
                    <li><strong>Language:</strong> ${language}</li>
                    <li><strong>User Agent:</strong> ${userAgent}</li>
                </ul>
            `;
            infoDiv.style.display = "block";

            // Use 2 Navigator Object Methods
            console.log("Javascript Enabled (Method 1):", navigator.javascriptEnabled?.());
            const beaconData = new Blob(["User clicked browser info"], { type: "text/plain" });
            navigator.sendBeacon?.("http://cs3-dev.ict.ru.ac.za/Practicals/4C3", beaconData); // Method 2
        });
    }

    // 17. Password Toggle Buttons (Re-integrated from previous step)
    document.querySelectorAll(".toggle-password").forEach(button => {
    button.addEventListener("click", function () {
        const targetIds = this.getAttribute("data-target").split(',');

        let anyHidden = false;

        targetIds.forEach(id => {
            const field = document.getElementById(id.trim());
            if (field) {
                if (field.type === "password") {
                    field.type = "text";
                    anyHidden = true;
                } else {
                    field.type = "password";
                }
            }
        });

        // Toggle button text depending on overall state
        this.textContent = anyHidden ? "Hide" : "Show";
    });
});

    

    // 18. Form Validation (Re-integrated from previous step)
    document.addEventListener("DOMContentLoaded", function () {
    const authForms = document.querySelectorAll(".auth-form");
    const registerForm = authForms[0];
    const loginForm = authForms[1];
    const registerErrorDiv = document.getElementById("registration-errors");
    const loginErrorDiv = document.getElementById("login-errors");

    // --- Register Form Validation ---
    if (registerForm) {
        registerForm.addEventListener("submit", function (e) {
            registerErrorDiv.innerHTML = '';

            const name = document.getElementById("regName").value.trim();
            const email = document.getElementById("regEmail").value.trim();
            const password = document.getElementById("regPassword").value;
            const confirmPassword = document.getElementById("regPasswordConfirm").value;
            const errors = [];

            // Name Validation
            if (!/^[a-zA-Z]{2,}$/.test(name)) {
                errors.push("Name must be at least 2 letters and contain only letters.");
            }

            // Email Validation
            if (!/^[^@]+@[^@]+\.[a-z]{2,}$/i.test(email)) {
                errors.push("Enter a valid email address.");
            }

            // Password Validation
            if (!/^(?=.*\d).{6,}$/.test(password)) {
                errors.push("Password must be at least 6 characters and contain at least one number.");
            }

            // Password Match Validation
            if (password !== confirmPassword) {
                errors.push("Passwords do not match.");
            }

            if (errors.length > 0) {
                e.preventDefault();
                registerErrorDiv.innerHTML = errors.map(err => `<div>• ${err}</div>`).join("");
            }
        });
    }

    // --- Login Form Validation ---
    if (loginForm) {
        loginForm.addEventListener("submit", function (e) {
            loginErrorDiv.innerHTML = '';

            const email = document.getElementById("loginEmail").value.trim();
            const password = document.getElementById("loginPassword").value;
            const errors = [];

            if (!/^[^@]+@[^@]+\.[a-z]{2,}$/i.test(email)) {
                errors.push("Enter a valid email address.");
            }

            if (password.length < 6) {
                errors.push("Password must be at least 6 characters.");
            }

            if (errors.length > 0) {
                e.preventDefault();
                loginErrorDiv.innerHTML = errors.map(err => `<div>• ${err}</div>`).join("");
            }
        });
    }

    // --- Display PHP Errors Inline ---
    const urlParams = new URLSearchParams(window.location.search);
    const signupError = urlParams.get("signupError");
    const loginError = urlParams.get("loginError");

    if (signupError) {
        const regErr = document.getElementById("registration-errors");
        if (regErr) regErr.innerHTML = `<div>• ${decodeURIComponent(signupError)}</div>`;
    }

    if (loginError) {
        const logErr = document.getElementById("login-errors");
        if (logErr) logErr.innerHTML = `<div>• ${decodeURIComponent(loginError)}</div>`;
    }

    //Toggle Password Visibility 
    const toggleButtons = document.querySelectorAll(".toggle-password");
    toggleButtons.forEach(btn => {
        btn.addEventListener("click", () => {
            const targetIds = btn.dataset.target.split(",");
            targetIds.forEach(id => {
                const input = document.getElementById(id.trim());
                if (input) {
                    input.type = (input.type === "password") ? "text" : "password";
                }
            });
            btn.textContent = btn.textContent === "Show" ? "Hide" : "Show";
        });
    });
});
    
});


//Toogle for FAQs
document.querySelectorAll(".faq-question").forEach(button => {
    button.addEventListener("click", () => {
      const answer = button.nextElementSibling;
      button.classList.toggle("active");

      if (answer.style.display === "block") {
        answer.style.display = "none";
      } else {
        answer.style.display = "block";
      }
    });
});


//Sidebar dropdown menu
document.querySelectorAll('.dropdown-toggle-sidebar').forEach(item =>{
    item.addEventListener('click' , e =>{
        e.preventDefault();
        item.parentElement.classList.toggle('open');
    });

});

//generate outfit button
document.getElementById('generateBtn').addEventListener('click', () => {
  const style = document.getElementById('style').value.trim();
  const mood = document.getElementById('mood').value.trim();

  if (!style) {
    alert('Please select a style.');
    return;
  }
  if (!mood) {
    alert('Please enter your mood.');
    return;
  }

  // Store selected values temporarily
  sessionStorage.setItem('selectedStyle', style);
  sessionStorage.setItem('selectedMood', mood);

 if (window.location.pathname.includes('index.php') || window.location.pathname === '/' || window.location.href.endsWith('/')) {
    window.location.href = `cartoon.php?style=${encodeURIComponent(style)}&mood=${encodeURIComponent(mood)}`;
  } 
  //If already on cartoon.php, just generate without reloading
  else if (window.location.pathname.includes('cartoon.php')) {
    generateOutfit();
  
}
});

//verify login
//localStorage.setItem('loggedIn', 'true'); 
//window.location.href = 'cartoon.html';

function saveOutfit(name, file) {
  const data = new URLSearchParams();
  data.append('outfit_name', name);
  data.append('outfit_file', file);

  fetch('save_outfit.php', {
    method: 'POST',
    body: data
  })
  .then(resp => resp.text())
  .then(text => alert(text))
  .catch(err => alert('Error saving outfit: ' + err.message));
}
