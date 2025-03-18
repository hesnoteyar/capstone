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

    <!-- Three.js & Dependencies -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fflate@0.7.4/umd/index.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three/examples/js/loaders/FBXLoader.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three/examples/js/controls/OrbitControls.js"></script>

    <title>3D Models Showcase</title>

    <style>
        /* Ensures footer stays at the bottom */
        html, body {
            height: 100%;
            margin: 0;
            display: flex;
            flex-direction: column;
        }
        .content {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
        }
        .canvas-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            width: 100%;
            max-width: 1200px;
            justify-content: center;
        }
        canvas {
            width: 100%;
            height: 300px;
            background-color: #222;
            border-radius: 10px;
        }
    </style>
</head>
<body class="bg-base-100 text-base-content">

    <div class="content">
        <h1 class="text-2xl font-bold text-center my-4">3D Motorcycle Battery Models</h1>
        
        <!-- Container for multiple 3D models -->
        <div class="canvas-container">
            <canvas id="canvas1"></canvas>
            <canvas id="canvas2"></canvas>
            <canvas id="canvas3"></canvas>
            <canvas id="canvas4"></canvas>
            <canvas id="canvas5"></canvas>
            <canvas id="canvas6"></canvas>
            <canvas id="canvas7"></canvas>
        </div>
    </div>

    <script>
        // Model Paths
        const modelPaths = [
            '../models/Motor_Battery/Motorcycle_Battery.fbx',
            '../models/Bike_Frame/Bike_Frame_texture.fbx',
            '../models/Motor_Battery/Chain_and_Sprockets_texture.fbx',
            '../models/Motor_Battery/Motorcycle_Frame_texture.fbx',
            '../models/Motor_Battery/Motorcycle_Tire_texture.fbx',
            '../models/Motor_Battery/Dual_Shock_texture.fbx',
        ];

        const scenes = [];
        const cameras = [];
        const renderers = [];
        const controls = [];

        // Initialize Scenes for each canvas
        function initScene(index, canvas) {
            const scene = new THREE.Scene();
            const camera = new THREE.PerspectiveCamera(75, canvas.clientWidth / canvas.clientHeight, 0.1, 1000);
            camera.position.set(0, 2, 8); // Set distance from model

            const renderer = new THREE.WebGLRenderer({ canvas, antialias: true, alpha: true });
            renderer.setSize(canvas.clientWidth, canvas.clientHeight);
            canvas.appendChild(renderer.domElement);

            const ambientLight = new THREE.AmbientLight(0xffffff, 0.7);
            scene.add(ambientLight);

            const directionalLight = new THREE.DirectionalLight(0xffffff, 1);
            directionalLight.position.set(5, 5, 5);
            scene.add(directionalLight);

            // Controls
            const orbitControls = new THREE.OrbitControls(camera, renderer.domElement);
            orbitControls.enableDamping = true;
            orbitControls.dampingFactor = 0.05;
            orbitControls.rotateSpeed = 0.5;
            orbitControls.zoomSpeed = 0.8;
            orbitControls.target.set(0, 0, 0);
            orbitControls.update();

            scenes.push(scene);
            cameras.push(camera);
            renderers.push(renderer);
            controls.push(orbitControls);
        }

        // Load Models
        function loadModel(index) {
            const loader = new THREE.FBXLoader();
            loader.load(modelPaths[index], function (object) {
                object.scale.set(0.05, 0.05, 0.05); // Adjust size
                object.position.set(0, -1, 0);
                scenes[index].add(object);
            }, undefined, function (error) {
                console.error(`Error loading model ${index + 1}:`, error);
            });
        }

        // Animate all scenes
        function animate() {
            requestAnimationFrame(animate);
            for (let i = 0; i < scenes.length; i++) {
                controls[i].update();
                renderers[i].render(scenes[i], cameras[i]);
            }
        }

        // Initialize everything
        document.addEventListener("DOMContentLoaded", () => {
            const canvases = document.querySelectorAll("canvas");
            canvases.forEach((canvas, index) => {
                initScene(index, canvas);
                loadModel(index);
            });

            animate();
        });

        // Resize handling
        window.addEventListener("resize", () => {
            for (let i = 0; i < renderers.length; i++) {
                const width = document.querySelectorAll("canvas")[i].clientWidth;
                const height = 300;
                renderers[i].setSize(width, height);
                cameras[i].aspect = width / height;
                cameras[i].updateProjectionMatrix();
            }
        });
    </script>

<?php include '../page/footer.php'; ?>

</body>
</html>
