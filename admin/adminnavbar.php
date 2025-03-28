<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.13/dist/full.min.css" rel="stylesheet" type="text/css" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        /* Apply the Poppins font family globally */
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>

    <script>
        // Check for saved theme preference or default to light
        const getTheme = () => {
            return localStorage.getItem('theme') || 'light'
        }
        
        // Apply theme
        const setTheme = (theme) => {
            document.documentElement.setAttribute('data-theme', theme)
            localStorage.setItem('theme', theme)
        }

        // Initialize theme
        document.addEventListener('DOMContentLoaded', () => {
            const savedTheme = getTheme()
            setTheme(savedTheme)
            const themeController = document.querySelector('.theme-controller')
            themeController.checked = savedTheme === 'dark'
            
            // Add event listener for theme toggle
            themeController.addEventListener('change', (e) => {
                const newTheme = e.target.checked ? 'dark' : 'light'
                setTheme(newTheme)
            })
        })
    </script>
</head>

<body>
    <div class="navbar bg-base-100 py-6 shadow-lg">
        <div class="navbar-start">
          <div class="dropdown">
            <div tabindex="0" role="button" class="btn btn-ghost btn-circle">
              <svg
                xmlns="http://www.w3.org/2000/svg"
                class="h-5 w-5"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor">
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M4 6h16M4 12h16M4 18h7" />
              </svg>
            </div>
            <ul
              tabindex="0"
                class="menu menu-sm dropdown-content bg-base-100 rounded-box z-[1] mt-3 w-52 p-2 shadow">
                <li><a href="..\admin\admin_dashboard.php">Dashboard</a></li>
                <li class="menu-title"><span style="color: red;">Product</span></li>
                <li><a href="..\admin\admin_addproduct.php">Inventory</a></li>
                <li><a href="..\admin\admin_purchases.php">Purchases</a></li>
                <li class="menu-title"><span style="color: red;">Employee</span></li>
                <li><a href="..\admin\admin_manageemployee.php">Manage Employees</a></li>
                <li><a href="..\admin\admin_addemployee.php">Add New Employee</a></li>
                <li><a href="..\admin\admin_payroll.php">Payroll</a></li>
                <li><a href="..\admin\admin_leave.php">Leave Request</a></li>
                <li><a href="..\admin\admin_schedule.php">Schedule Request</a></li>
                <li class="menu-title"><span style="color: red;">Security</span></li>
                <li><a href="..\admin\admin_logs.php">Audit Logs</a></li>
                <li><a href="..\authentication\adminlogout.php">Logout</a></li>
            </ul>
          </div>
        </div>
        <div class="flex justify-center w-full">
            <a class="">
                <img src="..\media\small_logo.png" alt="Logo" class="" />
            </a>
        </div>
        <div class="navbar-end space-x-4">
          <!-- Theme Toggle -->
          <label class="swap swap-rotate">
            <!-- this hidden checkbox controls the state -->
            <input type="checkbox" class="theme-controller" value="dark" />

            <!-- Sun icon -->
            <svg
              class="swap-off h-6 w-6 fill-current"
              xmlns="http://www.w3.org/2000/svg"
              viewBox="0 0 24 24">
              <path
                d="M5.64,17l-.71.71a1,1,0,0,0,0,1.41,1,1,0,0,0,1.41,0l.71-.71A1,1,0,0,0,5.64,17ZM5,12a1,1,0,0,0-1-1H3a1,1,0,0,0,0,2H4A1,1,0,0,0,5,12Zm7-7a1,1,0,0,0,1-1V3a1,1,0,0,0-2,0V4A1,1,0,0,0,12,5ZM5.64,7.05a1,1,0,0,0,.7.29,1,1,0,0,0,.71-.29,1,1,0,0,0,0-1.41l-.71-.71A1,1,0,0,0,4.93,6.34Zm12,.29a1,1,0,0,0,.7-.29l.71-.71a1,1,0,1,0-1.41-1.41L17,5.64a1,1,0,0,0,0,1.41A1,1,0,0,0,17.66,7.34ZM21,11H20a1,1,0,0,0,0,2h1a1,1,0,0,0,0-2Zm-9,8a1,1,0,0,0-1,1v1a1,1,0,0,0,2,0V20A1,1,0,0,0,12,19ZM18.36,17A1,1,0,0,0,17,18.36l.71.71a1,1,0,0,0,1.41,0,1,1,0,0,0,0-1.41ZM12,6.5A5.5,5.5,0,1,0,17.5,12,5.51,5.51,0,0,0,12,6.5Zm0,9A3.5,3.5,0,1,1,15.5,12,3.5,3.5,0,0,1,12,15.5Z" />
            </svg>

            <!-- Moon icon -->
            <svg
              class="swap-on h-6 w-6 fill-current"
              xmlns="http://www.w3.org/2000/svg"
              viewBox="0 0 24 24">
              <path
                d="M21.64,13a1,1,0,0,0-1.05-.14,8.05,8.05,0,0,1-3.37.73A8.15,8.15,0,0,1,9.08,5.49a8.59,8.59,0,0,1,.25-2A1,1,0,0,0,8,2.36,10.14,10.14,0,1,0,22,14.05,1,1,0,0,0,21.64,13Zm-9.5,6.69A8.14,8.14,0,0,1,7.08,5.22v.27A10.15,10.15,0,0,0,17.22,15.63a9.79,9.79,0,0,0,2.1-.22A8.11,8.11,0,0,1,12.14,19.73Z" />
            </svg>
          </label>
        </div>
      </div>
</body>
</html>
