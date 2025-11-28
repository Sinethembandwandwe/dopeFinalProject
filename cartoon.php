<?php
session_start();
if (!isset($_SESSION['firstname'])) {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>3D Outfit Generator</title>
<style>
body { margin: 0; overflow: hidden; font-family: sans-serif; }
#controls {
  position: absolute; top: 10px; left: 10px;
  background: rgba(255, 255, 255, 0.9);
  padding: 10px;
  z-index: 10;
  border-radius: 8px;
}
label { display: block; margin-bottom: 8px; }
select, button { margin-top: 6px; padding: 6px; border-radius: 4px; }
#saveOutfitBtn { background: #ff69b4; color: white; border: none; cursor: pointer; display: none; }
#viewOutfitsBtn { background: #b266ff; color: white; border: none; cursor: pointer; margin-left: 5px; }
#logoutBtn { background: #ff4d4d; color: white; border: none; cursor: pointer; margin-left: 5px; }
</style>
</head>
<body>
<div id="controls">
  <p>Welcome, <b><?php echo htmlspecialchars($_SESSION['firstname']); ?></b>!</p>
  <p id="weather-info">Loading weather...</p>

  <label>Filter Mode:
    <select id="filterMode">
      <option value="moodstyle">Mood + Style</option>
      <option value="special">Special Occasion</option>
    </select>
  </label>

  <div id="moodStyleControls">
    <label>Mood:
      <select id="mood">
        <option value="happy">Happy</option>
        <option value="sad">Sad</option>
      </select>
    </label>

    <label>Style:
      <select id="style">
        <option value="casual">Casual</option>
        <option value="formal">Formal</option>
        <option value="gothic">Gothic</option>
      </select>
    </label>
  </div>

  <div id="specialControls" style="display:none;">
    <label>Occasion:
      <select id="occasion">
        <option value="datenight">Date Night</option>
        <option value="wedding">Wedding</option>
        <option value="graduation">Graduation</option>
      </select>
    </label>
  </div>

  <button id="generateBtn">Generate Outfit</button>
  <button id="saveOutfitBtn">💾 Save Outfit</button>
  <button id="viewOutfitsBtn" onclick="window.location.href='view_outfits.php'">👗 View Saved Outfits</button>
  <button id="Btn" onclick="window.location.href='index%20.php'">Go to homepage</button>
  <button id="logoutBtn" onclick="window.location.href='/DOP{e}/logout.php'">🚪 Logout</button>
</div>

<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/build/three.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/loaders/GLTFLoader.js"></script>
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.5.1/dist/confetti.browser.min.js"></script>
<script src="cartoon.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function(){
  const saveBtn = document.getElementById("saveOutfitBtn");
  const generateBtn = document.getElementById("generateBtn");
  const filterSelect = document.getElementById("filterMode");
  const moodStyleControls = document.getElementById("moodStyleControls");
  const specialControls = document.getElementById("specialControls");

  filterSelect.addEventListener("change", ()=>{
    if(filterSelect.value === "special"){
      moodStyleControls.style.display = "none";
      specialControls.style.display = "block";
    } else {
      moodStyleControls.style.display = "block";
      specialControls.style.display = "none";
    }
  });

  generateBtn.addEventListener("click", ()=>{
    if(typeof generateOutfit === "function"){
      generateOutfit();
      saveBtn.style.display = "inline-block";
    } else console.error("generateOutfit() not found. Check cartoon.js file.");
  });

  saveBtn.addEventListener("click", ()=>{
    if(!window.currentOutfitPath){
      alert("Please generate an outfit first!");
      return;
    }
    fetch("save_outfit.php",{
      method:"POST",
      headers:{"Content-Type":"application/x-www-form-urlencoded"},
      body:"outfit_path="+encodeURIComponent(window.currentOutfitPath)
    })
    .then(res=>res.text())
    .then(data=>{
      alert(data);
      if(data.includes("successfully")) launchConfetti();
    })
    .catch(err=>alert("Error saving outfit: "+err));
  });

  function launchConfetti(){
    const duration = 2000;
    const animationEnd = Date.now() + duration;
    const defaults = { startVelocity:30, spread:360, ticks:60, zIndex:9999 };
    const interval = setInterval(()=>{
      const timeLeft = animationEnd - Date.now();
      if(timeLeft <= 0){ clearInterval(interval); return; }
      const particleCount = 50 * (timeLeft / duration);
      confetti(Object.assign({}, defaults, { particleCount, origin: { x:Math.random(), y:Math.random()-0.2 }}));
    }, 250);
  }
});
</script>
</body>
</html>
