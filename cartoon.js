let weatherData = { main: { temp: 22 }, weather: [{ main: "Clear" }] }; // fallback
let characterModel;
let characterPivot = null;
window.currentOutfitPath = null; // 🔥 global path for Save button

// Mood + Style catalog
const clothesCatalog = {
  "happy_casual": ["models/happy_formal1.glb","models/happy_party1.glb","models/happy_party3.glb","models/happy_party2.glb"],
  "happy_formal": ["models/jinnyformal.glb","models/jinnydress.glb", "models/happy_formal.glb"],
  "happy_gothic": ["models/goth_happy1.glb","models/goth_happy2.glb","models/jinnyclothes.glb"],
  "sad_casual": ["models/jinnysadcasual.glb","models/moddress3.glb","models/cold1.glb",],
  "sad_formal": ["models/jinnyformal.glb", "models/sad_formal.glb", "models/sad_formal1.glb"],
  "sad_gothic": ["models/goth_sad1.glb","models/goth_sad2.glb","models/goth_sad3.glb"]
};

// Special Occasions catalog (reusing old weather clothes)
const specialOccasionCatalog = {
  "datenight": ["models/sundress1.glb","models/sundress2.glb","models/datenight1.glb","models/datenight2.glb"],
  "wedding": ["models/moddress1.glb","models/moddress2.glb","models/wedding1.glb", "models/wedding2.glb"],
  "graduation": ["models/cold2.glb","models/graduation1.glb", "models/graduation2.glb"]
};

const loadedClothes = {};       
const loadedSpecialClothes = {}; 

// Three.js scene setup
const scene = new THREE.Scene();
const camera = new THREE.PerspectiveCamera(45, window.innerWidth / window.innerHeight, 0.1, 1000);
camera.position.set(0, 2, 2);

const renderer = new THREE.WebGLRenderer({ antialias: true });
renderer.setSize(window.innerWidth, window.innerHeight);
renderer.setClearColor(0xbfd1e5, 1);
document.body.appendChild(renderer.domElement);

const light = new THREE.DirectionalLight(0xffffff, 0.7);
light.position.set(2, 2, 5);
scene.add(light);

const ambientLight = new THREE.AmbientLight(0x404040, 0.3);
scene.add(ambientLight);

const gltfLoader = new THREE.GLTFLoader();
let currentOutfit = null;

// Save button
window.addEventListener('DOMContentLoaded', () => {
  const saveOutfitBtn = document.createElement('button');
  saveOutfitBtn.id = 'saveOutfitBtn';
  saveOutfitBtn.textContent = 'Save Outfit';
  saveOutfitBtn.disabled = true;
  document.getElementById('controls').appendChild(saveOutfitBtn);

  saveOutfitBtn.addEventListener('click', () => {
    if (!window.currentOutfitPath) {
      alert('Please generate the outfit first!');
      return;
    }

    fetch("save_outfit.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: "outfit_path=" + encodeURIComponent(window.currentOutfitPath)
    })
    .then(res => res.text())
    .then(data => alert(data))
    .catch(err => alert("Error saving outfit: " + err));

    saveOutfitBtn.disabled = true;
  });
});

// Load base character
gltfLoader.load(
  "models/jinny.glb",
  function (gltf) {
    characterModel = gltf.scene;
    characterModel.scale.set(0.5, 0.5, 0.5);

    characterPivot = new THREE.Object3D();
    scene.add(characterPivot);

    const box = new THREE.Box3().setFromObject(characterModel);
    const center = box.getCenter(new THREE.Vector3());
    characterModel.position.sub(center);

    characterPivot.position.y = 1.2;
    characterPivot.add(characterModel);
    characterPivot.scale.set(1.5, 1.5, 1.5);

    loadAllClothes(characterPivot, () => console.log("✅ All mood/style clothes loaded"));
    loadAllSpecialClothes(characterPivot, () => console.log("✅ All special occasion clothes loaded"));

    console.log("✅ Character model loaded");
  },
  undefined,
  function (error) {
    console.error("❌ Failed to load character model:", error);
  }
);

function loadAllClothes(characterPivot, onComplete) {
  const categories = Object.keys(clothesCatalog);
  let totalToLoad = 0, loadedCount = 0;

  categories.forEach(cat => {
    loadedClothes[cat] = [];
    totalToLoad += clothesCatalog[cat].length;
  });

  categories.forEach(category => {
    clothesCatalog[category].forEach(url => {
      gltfLoader.load(url, gltf => {
        const clothes = gltf.scene;
        clothes.visible = false;
        clothes.scale.set(0.5, 0.5, 0.5);
        characterPivot.add(clothes);
        loadedClothes[category].push({mesh: clothes, path: url});

        loadedCount++;
        if (loadedCount === totalToLoad) onComplete();
      }, undefined, error => {
        console.error(`Failed to load mood/style model at ${url}`, error);
        loadedCount++;
        if (loadedCount === totalToLoad) onComplete();
      });
    });
  });
}

function loadAllSpecialClothes(characterPivot, onComplete) {
  const categories = Object.keys(specialOccasionCatalog);
  let totalToLoad = 0, loadedCount = 0;

  categories.forEach(cat => {
    loadedSpecialClothes[cat] = [];
    totalToLoad += specialOccasionCatalog[cat].length;
  });

  categories.forEach(category => {
    specialOccasionCatalog[category].forEach(url => {
      gltfLoader.load(url, gltf => {
        const clothes = gltf.scene;
        clothes.visible = false;
        clothes.scale.set(0.5, 0.5, 0.5);
        characterPivot.add(clothes);
        loadedSpecialClothes[category].push({mesh: clothes, path: url});

        loadedCount++;
        if (loadedCount === totalToLoad) onComplete();
      }, undefined, error => {
        console.error(`Failed to load special model at ${url}`, error);
        loadedCount++;
        if (loadedCount === totalToLoad) onComplete();
      });
    });
  });
}

// Mood + Style outfit
function changeOutfit(mood, style) {
  Object.values(loadedClothes).forEach(arr => arr.forEach(c => c.mesh.visible = false));
  Object.values(loadedSpecialClothes).forEach(arr => arr.forEach(c => c.mesh.visible = false));

  const category = `${mood}_${style}`;
  const clothesArray = loadedClothes[category];
  if (!clothesArray || clothesArray.length === 0) return console.warn(`No clothes for ${category}`);

  const randomIndex = Math.floor(Math.random() * clothesArray.length);
  clothesArray[randomIndex].mesh.visible = true;

  currentOutfit = {
    type: 'moodstyle',
    mood,
    style,
    category,
    variant: randomIndex,
    path: clothesArray[randomIndex].path,
    timestamp: new Date().toISOString()
  };

  window.currentOutfitPath = currentOutfit.path;
  document.getElementById('saveOutfitBtn').disabled = false;
  console.log(`Outfit: ${category} variant #${randomIndex + 1}`);
}

// Special Occasion outfit
function changeOutfitBySpecial(occasion) {
  Object.values(loadedClothes).forEach(arr => arr.forEach(c => c.mesh.visible = false));
  Object.values(loadedSpecialClothes).forEach(arr => arr.forEach(c => c.mesh.visible = false));

  const clothesArray = loadedSpecialClothes[occasion];
  if (!clothesArray || clothesArray.length === 0) return console.warn(`No clothes for occasion: ${occasion}`);

  const randomIndex = Math.floor(Math.random() * clothesArray.length);
  clothesArray[randomIndex].mesh.visible = true;

  currentOutfit = {
    type: 'special',
    occasion,
    variant: randomIndex,
    path: clothesArray[randomIndex].path,
    timestamp: new Date().toISOString()
  };

  window.currentOutfitPath = currentOutfit.path;
  document.getElementById('saveOutfitBtn').disabled = false;
  console.log(`Special outfit: ${occasion} variant #${randomIndex + 1}`);
}

// Animate
let rotateDirection = 1, rotateSpeed = 0.01, rotateLimit = 0.5;
function animate() {
  requestAnimationFrame(animate);
  if (characterPivot) {
    characterPivot.rotation.y += rotateSpeed * rotateDirection;
    if (characterPivot.rotation.y > rotateLimit) rotateDirection = -1;
    else if (characterPivot.rotation.y < -rotateLimit) rotateDirection = 1;
  }
  renderer.render(scene, camera);
}
animate();


// Weather fetching
function getWeather(callback) {
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(success, error);
  } else callback();

  function success(pos) {
    const lat = pos.coords.latitude;
    const lon = pos.coords.longitude;
    const API_KEY = "actualKeyRedacted"; // your key

    fetch(`https://api.openweathermap.org/data/2.5/weather?lat=${lat}&lon=${lon}&units=metric&appid=${API_KEY}`)
      .then(res => res.json())
      .then(data => {
        weatherData = data;
        document.getElementById("weather-info").innerText = `Weather: ${data.weather[0].main}, ${data.main.temp}°C`;
        callback();
      })
      .catch(() => { console.warn("Weather fetch failed, using fallback"); callback(); });
  }

  function error() { console.warn("Geolocation failed"); callback(); }
}


// Generate outfit
function generateOutfit() {
  getWeather(() => {
    const filterMode = document.getElementById("filterMode").value;
    if(filterMode === "special"){
      const occasion = document.getElementById("occasion").value;
      changeOutfitBySpecial(occasion);
    } else {
      const mood = document.getElementById("mood").value;
      const style = document.getElementById("style").value;
      changeOutfit(mood, style);
    }
  });
}

// Get weather category (optional)
function getWeatherCategory(temp){
  if(temp < 15) return "cold";
  else if(temp < 25) return "moderate";
  else return "hot";
}