<?php
session_start();
include '../authentication/db.php';
include '../page/topnavbar.php';

// Assuming user_id is stored in the session
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.0.0/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/daisyui@1.1.4/dist/full.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.9.1/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fflate@0.7.4/umd/index.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three/examples/js/loaders/FBXLoader.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three/examples/js/controls/OrbitControls.js"></script>

    <title>3D Motorcycle Models</title>
    <style>
        html, body {
            height: 100%;
            margin: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            background-color: #1a1a1a; /* Dark background */
        }

        .models-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            max-width: 1200px;
            margin-top: 20px;
        }

        .model-card {
            background: #2a2a2a;
            padding: 10px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        }

        canvas {
            width: 100%;
            height: 300px;
            display: block;
        }
    </style>
</head>
<body class="bg-gray-900 text-white">

    <h1 class="text-3xl font-bold mt-6">3D Motorcycle Models</h1>
    <div class="models-container" id="modelsContainer">
        <!-- Canvases will be created here dynamically -->
    </div>

    <script>
        const models = [
            { name: "Motorcycle Battery", path: "../models/Motor_Battery/Motorcycle_Battery.fbx" },
            { name: "Motorcycle Frame", path: "../models/Bike_Frame/Bike_Frame_texture.fbx" },
            { name: "Motorcycle Sprocket", path: ".../models/Motor_Battery/Chain_and_Sprockets_texture.fbx" },
            { name: "Motorcycle Frame", path: "../models/Motor_Battery/Motorcycle_Frame_texture.fbx" },
            { name: "Motorcycle Tire", path: "../models/Motor_Battery/Motorcycle_Tire_texture.fbx" },
            { name: "Motorcycle Handlebar", path: "../models/Motor_Handlebar/Motorcycle_Handlebar.fbx" },
            { name: "Motorcycle Shock", path: "../models/Motor_Battery/Dual_Shock_texture.fbx" },
        ];

        function createModelViewer(model) {
            // Create container
            const card = document.createElement("div");
            card.className = "model-card";

            // Create title
            const title = document.createElement("h2");
            title.className = "text-lg font-semibold";
            title.innerText = model.name;
            card.appendChild(title);

            // Create canvas
            const canvas = document.createElement("canvas");
            canvas.width = 300;
            canvas.height = 300;
            card.appendChild(canvas);

            document.getElementById("modelsContainer").appendChild(card);

            // Setup Three.js
            const scene = new THREE.Scene();
            const camera = new THREE.PerspectiveCamera(75, 1, 0.1, 1000);
            camera.position.set(0, 2, 6);

            const renderer = new THREE.WebGLRenderer({ canvas, antialias: true, alpha: true });
            renderer.setSize(300, 300);

            // Lighting
            const ambientLight = new THREE.AmbientLight(0xffffff, 0.6);
            scene.add(ambientLight);

            const directionalLight = new THREE.DirectionalLight(0xffffff, 1);
            directionalLight.position.set(5, 5, 5);
            scene.add(directionalLight);

            // Controls
            const controls = new THREE.OrbitControls(camera, renderer.domElement);
            controls.enableDamping = true;
            controls.dampingFactor = 0.05;
            controls.rotateSpeed = 0.5;
            controls.zoomSpeed = 0.8;

            // Load Model
            const loader = new THREE.FBXLoader();
            loader.load(model.path, function (object) {
                object.scale.set(0.05, 0.05, 0.05);
                object.position.set(0, -1, 0);
                scene.add(object);

                // Animation Loop
                function animate() {
                    requestAnimationFrame(animate);
                    controls.update();
                    renderer.render(scene, camera);
                }
                animate();
            }, undefined, function (error) {
                console.error(`Error loading ${model.name}:`, error);
            });

            // Handle Resizing
            window.addEventListener("resize", () => {
                const size = 300;
                renderer.setSize(size, size);
                camera.aspect = 1;
                camera.updateProjectionMatrix();
            });
        }

        // Create viewers for each model
        models.forEach(createModelViewer);
    </script>

</body>
</html>
