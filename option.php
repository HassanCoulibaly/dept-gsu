<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<style>
    :root {
        --primary-blue: #4464FF;
        --secondary-gray: #EFEFEF;
        --text-color-dark: #333333;
        --text-color-light: #6A6A6A;
        --bg-color-light: #F8F9FA;
        --bg-color-white: #FFFFFF;
    }

    body {
        margin: 0;
        padding: 0;
        font-family: 'Poppins', sans-serif;
        background-color: var(--bg-color-light);
        color: var(--text-color-dark);
    }
    
    .dashboard-container {
        display: flex;
        min-height: 100vh;
    }

    /* Sidebar Styling */
    .sidebar {
        width: 250px;
        background-color: var(--bg-color-white);
        padding: 2rem 1rem;
        box-shadow: 2px 0 10px rgba(0, 0, 0, 0.05);
        display: flex;
        flex-direction: column;
    }

    .logo-container {
        font-size: 1.5rem;
        font-weight: 600;
        color: var(--primary-blue);
        margin-bottom: 2rem;
        text-align: center;
    }

    .nav-link {
        display: flex;
        align-items: center;
        padding: 12px 16px;
        margin-bottom: 0.5rem;
        color: var(--text-color-light);
        text-decoration: none;
        border-radius: 8px;
        transition: background-color 0.2s, color 0.2s;
    }

    .nav-link:hover, .nav-link.active {
        background-color: var(--primary-blue);
        color: var(--bg-color-white);
    }

    .nav-link:hover h2, .nav-link.active h2 {
        color: var(--bg-color-white);
    }
    
    .nav-link h2 {
        font-size: 1rem;
        font-weight: 500;
        margin: 0 0 0 12px;
        color: var(--text-color-dark);
        transition: color 0.2s;
    }

    /* Main Content Area */
    .main-content {
        flex-grow: 1;
        padding: 2rem;
    }

    .header-section {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
    }

    .header-section h1 {
        font-size: 2rem;
        font-weight: 600;
        margin: 0;
    }

    .back-btn {
        display: flex;
        align-items: center;
        text-decoration: none;
        color: var(--text-color-light);
        font-size: 1rem;
        transition: color 0.2s;
    }

    .back-btn:hover {
        color: var(--primary-blue);
    }

    .cards-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.5rem;
    }

    .info-card {
        background-color: var(--bg-color-white);
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .info-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
    }

    .info-card h3 {
        font-size: 1.25rem;
        font-weight: 600;
        margin-top: 0;
        margin-bottom: 0.5rem;
    }

    .info-card p {
        color: var(--text-color-light);
        margin: 0;
    }

    /* Utility classes for icons (replace with actual SVG or font icons) */
    .icon {
        width: 24px;
        height: 24px;
        background-color: var(--text-color-light);
        -webkit-mask-size: cover;
        mask-size: cover;
    }

    .icon-dashboard { -webkit-mask-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath fill='currentColor' d='M3 13h8V3H3zm0 8h8v-6H3zm10 0h8V11h-8zm0-18v6h8V3z'/%3E%3C/svg%3E"); }
    .icon-success { -webkit-mask-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath fill='currentColor' d='M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z'/%3E%3C/svg%3E"); }
    .icon-projects { -webkit-mask-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath fill='currentColor' d='M21 16H3a1 1 0 0 0-1 1v4a1 1 0 0 0 1 1h18a1 1 0 0 0 1-1v-4a1 1 0 0 0-1-1zm0-14H3a1 1 0 0 0-1 1v4a1 1 0 0 0 1 1h18a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1zm0 7H3a1 1 0 0 0-1 1v4a1 1 0 0 0 1 1h18a1 1 0 0 0 1-1v-4a1 1 0 0 0-1-1z'/%3E%3C/svg%3E"); }
</style>
</head>
<body>

<div class="dashboard-container">
    <aside class="sidebar">
        <div class="logo-container">Admin Dashboard</div>
        <a href="dashboard.php" class="nav-link active">
            <span class="icon icon-dashboard"></span>
            <h2>Dashboard</h2>
        </a>
        <a href="success_dashboard.php" class="nav-link">
            <span class="icon icon-success"></span>
            <h2>Success Stories</h2>
        </a>
        <a href="project_dashboard.php" class="nav-link">
            <span class="icon icon-projects"></span>
            <h2>Projects</h2>
        </a>
    </aside>

    <div class="main-content">
        <div class="header-section">
            <h1>Welcome, Admin</h1>
            <a href="index.php" class="back-btn">
                ← Back
            </a>
        </div>
        
        <div class="cards-container">
            <div class="info-card">
                <h3>Card Title 1</h3>
                <p>Some brief, relevant information goes here.</p>
            </div>
            <div class="info-card">
                <h3>Card Title 2</h3>
                <p>More details or a quick summary.</p>
            </div>
            <div class="info-card">
                <h3>Card Title 3</h3>
                <p>This space can be used for key metrics or alerts.</p>
            </div>
        </div>
    </div>
</div>

</body>
</html>