<style>
    /* Reset and Base Styles */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    :root {
        --primary-color: #4299e1;
        --primary-dark: #3182ce;
        --secondary-color: #2d3748;
        --success-color: #48bb78;
        --warning-color: #ed8936;
        --danger-color: #f56565;
        --background-color: #f7fafc;
        --text-primary: #2d3748;
        --text-secondary: #718096;
        --border-color: #e2e8f0;
        --sidebar-width: 280px;
    }

    body {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        background-color: var(--background-color);
        color: var(--text-primary);
        line-height: 1.5;
        display: flex;
        min-height: 100vh;
    }

    /* Main Content Area */
    .main-content {
        flex: 1;
        margin-left: var(--sidebar-width);
        padding: 2rem;
        max-width: 100%;
    }

    /* Page Header */
    .page-header {
        margin-bottom: 2rem;
        padding: 1.5rem;
        background: white;
        border-radius: 10px;
        box-shadow: var(--card-shadow);
    }

    .page-header h1 {
        color: var(--primary-color);
        font-size: 1.8rem;
        margin-bottom: 0.5rem;
    }

    .page-header p {
        color: var(--text-secondary);
        font-size: 1.1rem;
    }

    /* Dashboard Cards */
    .dashboard-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
        gap: 1.5rem;
        margin-top: 1.5rem;
    }

    .card {
        background: white;
        border-radius: 10px;
        padding: 1.5rem;
        box-shadow: var(--card-shadow);
    }

    .card-title {
        display: flex;
        align-items: center;
        color: var(--text-primary);
        font-size: 1.25rem;
        margin-bottom: 1.5rem;
    }

    .card-title i {
        margin-right: 0.75rem;
        color: var(--primary-color);
        font-size: 1.4rem;
    }

    /* Election Cards */
    .election-card {
        background: white;
        border-radius: 8px;
        padding: 1.5rem;
        margin-bottom: 1rem;
        border: 1px solid var(--border-color);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .election-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .election-title {
        color: var(--primary-color);
        font-size: 1.2rem;
        margin-bottom: 1rem;
    }

    .election-description {
        color: var(--text-secondary);
        margin-bottom: 1rem;
    }

    .election-date {
        display: flex;
        align-items: flex-start;
        color: var(--text-secondary);
        margin-bottom: 1rem;
        font-size: 0.95rem;
    }

    .election-date i {
        margin-right: 0.5rem;
        margin-top: 0.25rem;
        color: var(--info-color);
    }

    /* Badges */
    .badge {
        display: inline-block;
        padding: 0.4rem 0.8rem;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 500;
        text-transform: uppercase;
    }

    .badge-upcoming {
        background-color: rgba(255, 193, 7, 0.15);
        color: #856404;
    }

    /* Election Status */
    .election-status {
        margin-top: 1rem;
        text-align: center;
    }

    .voting-info {
        margin-top: 0.5rem;
        color: var(--text-secondary);
        font-style: italic;
    }

    /* Buttons */
    .btn {
        display: inline-flex;
        align-items: center;
        padding: 0.75rem 1.5rem;
        border-radius: 5px;
        text-decoration: none;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn i {
        margin-right: 0.5rem;
    }

    .btn-primary {
        background-color: var(--primary-color);
        color: white;
        border: none;
    }

    .btn-primary:hover {
        background-color: var(--primary-dark);
        transform: translateY(-1px);
    }

    /* User Information */
    .card p {
        margin-bottom: 0.75rem;
        color: var(--text-secondary);
    }

    .card p strong {
        color: var(--text-primary);
        margin-right: 0.5rem;
    }

    /* Responsive Design */
    @media (max-width: 1200px) {
        .dashboard-cards {
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        }
    }

    @media (max-width: 768px) {
        body {
            padding-top: 4rem;
        }
        
        .sidebar {
            transform: translateX(-100%);
            transition: transform 0.3s ease;
        }
        
        .sidebar.show {
            transform: translateX(0);
        }
        
        .mobile-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .main-content {
            margin-left: 0;
            padding: 1rem;
        }
    }

    /* Sidebar Styles */
    .sidebar {
        width: var(--sidebar-width);
        background: white;
        height: 100vh;
        position: fixed;
        left: 0;
        top: 0;
        padding: 1.5rem;
        display: flex;
        flex-direction: column;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        z-index: 1000;
    }

    .student-info {
        text-align: center;
        padding-bottom: 1.5rem;
        margin-bottom: 1.5rem;
        border-bottom: 1px solid var(--border-color);
    }

    .student-photo {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background: var(--border-color);
        margin: 0 auto 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .student-photo i {
        font-size: 2.5rem;
        color: var(--text-secondary);
    }

    .student-info h2 {
        font-size: 1.25rem;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
    }

    .student-department,
    .student-roll {
        font-size: 0.875rem;
        color: var(--text-secondary);
        margin-bottom: 0.25rem;
    }

    .nav-links {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        flex-grow: 1;
    }

    .nav-links a {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem 1rem;
        color: var(--text-primary);
        text-decoration: none;
        border-radius: 0.5rem;
        transition: all 0.2s;
    }

    .nav-links a:hover {
        background: rgba(66, 153, 225, 0.1);
        color: var(--primary-color);
    }

    .nav-links a.active {
        background: var(--primary-color);
        color: white;
    }

    .nav-links i {
        width: 1.5rem;
        text-align: center;
    }

    .logout-form {
        margin-top: auto;
    }

    .logout-btn {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.75rem;
        padding: 0.75rem;
        background: var(--danger-color);
        color: white;
        border: none;
        border-radius: 0.5rem;
        cursor: pointer;
        font-size: 1rem;
        transition: all 0.2s;
    }

    .logout-btn:hover {
        background: #e53e3e;
    }

    .mobile-header {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        padding: 1rem;
        background: white;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        z-index: 900;
    }

    .menu-toggle {
        background: none;
        border: none;
        font-size: 1.5rem;
        color: var(--text-primary);
        cursor: pointer;
        padding: 0.5rem;
    }

    .mobile-header h1 {
        font-size: 1.25rem;
        margin: 0;
        text-align: center;
    }

    /* Alert styles */
    .alert {
        padding: 1rem;
        border-radius: 0.5rem;
        margin-bottom: 1rem;
    }

    .alert-success {
        background-color: #c6f6d5;
        color: #2f855a;
        border: 1px solid #9ae6b4;
    }

    .alert-error {
        background-color: #fed7d7;
        color: #c53030;
        border: 1px solid #feb2b2;
    }

    /* Card styles */
    .card {
        background: white;
        border-radius: 0.5rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }

    /* Button styles */
    .btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.5rem;
        border-radius: 0.5rem;
        font-size: 1rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
        border: none;
    }

    .btn-secondary {
        background: var(--text-secondary);
        color: white;
    }

    .btn-secondary:hover {
        background: var(--secondary-color);
    }

    /* Form styles */
    .form-group {
        margin-bottom: 1rem;
    }

    .form-label {
        display: block;
        margin-bottom: 0.5rem;
        color: var(--text-primary);
        font-weight: 500;
    }

    .form-control {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid var(--border-color);
        border-radius: 0.5rem;
        font-size: 1rem;
        transition: border-color 0.2s;
    }

    .form-control:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.2);
    }

    /* Table styles */
    .table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 1rem;
    }

    .table th,
    .table td {
        padding: 0.75rem;
        border-bottom: 1px solid var(--border-color);
        text-align: left;
    }

    .table th {
        background: #f8fafc;
        font-weight: 600;
        color: var(--text-primary);
    }

    .table tr:hover {
        background: #f8fafc;
    }

    /* Badge styles */
    .badge {
        display: inline-block;
        padding: 0.25rem 0.5rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 500;
    }

    .badge-success {
        background: #c6f6d5;
        color: #2f855a;
    }

    .badge-warning {
        background: #feebc8;
        color: #c05621;
    }

    .badge-danger {
        background: #fed7d7;
        color: #c53030;
    }
</style>

<!-- Font Awesome and Google Fonts -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">