<style>
    :root {
        --primary-color: #4361ee;
        --secondary-color: #3f37c9;
        --success-color: #4cc9f0;
        --warning-color: #f72585;
        --text-primary: #2b2d42;
        --text-secondary: #8d99ae;
        --bg-light: #f8f9fa;
        --border-radius: 12px;
        --card-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
        --transition: all 0.3s ease;
        --gradient-primary: linear-gradient(135deg, #4361ee, #3f37c9);
        --gradient-success: linear-gradient(135deg, #4cc9f0, #4895ef);
        --gradient-warning: linear-gradient(135deg, #f72585, #b5179e);
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Poppins', sans-serif;
        background: var(--bg-light);
        min-height: 100vh;
    }

    .dashboard {
        display: flex;
        min-height: 100vh;
    }

    /* Sidebar Styles */
    .sidebar {
        width: 250px;
        background: white;
        padding: 20px;
        box-shadow: var(--card-shadow);
        position: fixed;
        height: 100vh;
        overflow-y: auto;
    }

    .sidebar-header {
        text-align: center;
        padding-bottom: 20px;
        border-bottom: 1px solid #e9ecef;
        margin-bottom: 20px;
    }

    .sidebar-header h2 {
        color: var(--text-primary);
        font-size: 1.5rem;
        margin-bottom: 5px;
    }

    .sidebar-header p {
        color: var(--text-secondary);
        font-size: 0.9rem;
    }

    .nav-menu {
        list-style: none;
    }

    .nav-item {
        margin-bottom: 10px;
    }

    .nav-link {
        display: flex;
        align-items: center;
        padding: 12px 15px;
        color: var(--text-primary);
        text-decoration: none;
        border-radius: var(--border-radius);
        transition: var(--transition);
    }

    .nav-link i {
        margin-right: 10px;
        width: 20px;
        text-align: center;
    }

    .nav-link:hover, .nav-link.active {
        background: rgba(67, 97, 238, 0.1);
        color: var(--primary-color);
    }

    /* Main Content Styles */
    .main-content {
        flex: 1;
        padding: 20px;
        margin-left: 250px;
    }

    .top-bar {
        background: white;
        border-radius: var(--border-radius);
        padding: 1.5rem;
        margin-bottom: 2rem;
        box-shadow: var(--card-shadow);
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: relative;
        overflow: hidden;
    }

    .top-bar::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: var(--gradient-primary);
        opacity: 0.03;
        z-index: 0;
    }

    .page-title {
        font-size: 1.8rem;
        color: var(--text-primary);
        margin: 0;
        position: relative;
        z-index: 1;
        font-weight: 600;
    }

    .btn-warning {
        background: var(--gradient-warning);
        color: white;
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: var(--border-radius);
        font-weight: 500;
        transition: var(--transition);
        position: relative;
        z-index: 1;
    }

    .btn-warning:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(247, 37, 133, 0.3);
    }

    /* Form Styles */
    .form-group {
        margin-bottom: 1rem;
    }

    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        color: var(--text-primary);
    }

    .form-control {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #ddd;
        border-radius: var(--border-radius);
        font-family: inherit;
        font-size: 1rem;
    }

    .form-control:focus {
        outline: none;
        border-color: var(--primary-color);
    }

    /* Alert Styles */
    .alert {
        padding: 1rem;
        border-radius: var(--border-radius);
        margin-bottom: 1rem;
    }

    .alert-success {
        background: #d4edda;
        color: #155724;
    }

    .alert-danger {
        background: #f8d7da;
        color: #721c24;
    }

    /* Card Styles */
    .card {
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--card-shadow);
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .card-header {
        margin-bottom: 1rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #e9ecef;
    }

    .card-title {
        color: var(--text-primary);
        font-size: 1.25rem;
        margin: 0;
    }

    /* Candidate Card Styles */
    .candidates-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 2rem;
        padding: 1rem;
    }

    .candidate-card {
        display: flex;
        flex-direction: column;
        transition: var(--transition);
    }

    .candidate-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
    }

    .candidate-image {
        width: 225px;  /* Fixed width */
        height: 300px; /* Fixed height for 3:4 ratio */
        margin: 0 auto 1rem;
        position: relative;
        border-radius: 12px;
        overflow: hidden;
        background: white;
        padding: 8px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        border: 3px solid var(--primary-color);
    }

    .candidate-image::before {
        content: '';
        position: absolute;
        top: -2px;
        left: -2px;
        right: -2px;
        bottom: -2px;
        background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
        border-radius: 14px;
        z-index: -1;
        opacity: 0.5;
    }

    .candidate-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 8px;
    }

    .candidate-image .no-image {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f8f9fa;
        color: var(--text-secondary);
        font-size: 3rem;
    }

    .candidate-name {
        font-size: 1.2rem;
        color: var(--text-primary);
        margin: 0.5rem 0;
        text-align: center;
    }

    .candidate-department {
        color: var(--text-secondary);
        font-size: 0.9rem;
        text-align: center;
        margin-bottom: 1rem;
    }

    .candidate-bio {
        color: var(--text-secondary);
        font-size: 0.9rem;
        margin-bottom: 1rem;
        line-height: 1.5;
    }

    .candidate-percentage {
        color: var(--success-color);
        font-weight: 500;
        margin-bottom: 1rem;
    }

    .card-footer {
        margin-top: auto;
        display: flex;
        gap: 1rem;
        justify-content: center;
    }

    .btn-danger {
        background: var(--warning-color);
        color: white;
    }

    /* Dashboard Specific Styles */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.5rem;
        padding: 1rem;
    }

    .stats-grid .card {
        background: white;
        border-radius: var(--border-radius);
        padding: 1.5rem;
        position: relative;
        overflow: hidden;
        transition: var(--transition);
        border: none;
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
    }

    .stats-grid .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
    }

    .stats-grid .card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: var(--gradient-primary);
        opacity: 0.05;
        z-index: 0;
    }

    .stats-grid .card:nth-child(1)::before {
        background: var(--gradient-primary);
    }

    .stats-grid .card:nth-child(2)::before {
        background: var(--gradient-success);
    }

    .stats-grid .card:nth-child(3)::before {
        background: var(--gradient-warning);
    }

    .stats-grid .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        position: relative;
        z-index: 1;
        border: none;
        padding: 0;
    }

    .stats-grid .card-title {
        font-size: 1.1rem;
        color: var(--text-primary);
        font-weight: 600;
        margin: 0;
    }

    .stats-grid .card-header i {
        font-size: 2rem;
        background: var(--gradient-primary);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        opacity: 0.8;
    }

    .stats-grid .card:nth-child(1) .card-header i {
        background: var(--gradient-primary);
        -webkit-background-clip: text;
    }

    .stats-grid .card:nth-child(2) .card-header i {
        background: var(--gradient-success);
        -webkit-background-clip: text;
    }

    .stats-grid .card:nth-child(3) .card-header i {
        background: var(--gradient-warning);
        -webkit-background-clip: text;
    }

    .stats-grid .card-body {
        position: relative;
        z-index: 1;
    }

    .stats-grid .card-body h2 {
        font-size: 2.5rem;
        font-weight: 700;
        margin: 0;
        background: var(--gradient-primary);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .stats-grid .card:nth-child(1) .card-body h2 {
        background: var(--gradient-primary);
        -webkit-background-clip: text;
    }

    .stats-grid .card:nth-child(2) .card-body h2 {
        background: var(--gradient-success);
        -webkit-background-clip: text;
    }

    .stats-grid .card:nth-child(3) .card-body h2 {
        background: var(--gradient-warning);
        -webkit-background-clip: text;
    }

    /* Activity Feed Styles */
    .activity-feed {
        background: white;
        border-radius: var(--border-radius);
        padding: 1.5rem;
        margin-top: 2rem;
        box-shadow: var(--card-shadow);
    }

    .activity-feed h3 {
        color: var(--text-primary);
        font-size: 1.2rem;
        margin-bottom: 1.5rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid var(--bg-light);
    }

    .activity-item {
        display: flex;
        align-items: center;
        padding: 1rem 0;
        border-bottom: 1px solid var(--bg-light);
    }

    .activity-item:last-child {
        border-bottom: none;
    }

    .activity-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 1rem;
        font-size: 1.2rem;
        background: var(--gradient-primary);
        color: white;
    }

    .activity-icon.vote {
        background: var(--gradient-success);
    }

    .activity-details {
        flex: 1;
    }

    .activity-title {
        color: var(--text-primary);
        font-size: 0.95rem;
        margin: 0;
        font-weight: 500;
    }

    .activity-time {
        color: var(--text-secondary);
        font-size: 0.85rem;
        margin: 0.25rem 0 0;
    }

    @media (max-width: 768px) {
        .candidates-grid {
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        }

        .candidate-image {
            width: 180px;
            height: 240px;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }

        .stats-grid .card {
            padding: 1.25rem;
        }

        .stats-grid .card-body h2 {
            font-size: 2rem;
        }

        .top-bar {
            flex-direction: column;
            gap: 1rem;
            text-align: center;
        }

        .btn-warning {
            width: 100%;
        }
    }

    /* Department Card Styles */
    .departments-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 1.5rem;
        padding: 1rem;
    }

    .department-card {
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--card-shadow);
        transition: var(--transition);
        border: none;
        overflow: hidden;
    }

    .department-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
    }

    .department-card .card-header {
        padding: 1.5rem;
        border-bottom: 1px solid var(--bg-light);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .department-name {
        font-size: 1.2rem;
        color: var(--text-primary);
        margin: 0;
        font-weight: 600;
    }

    .department-status {
        padding: 0.4rem 0.8rem;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 500;
    }

    .status-active {
        background: rgba(76, 201, 240, 0.1);
        color: var(--success-color);
    }

    .status-inactive {
        background: rgba(247, 37, 133, 0.1);
        color: var(--warning-color);
    }

    .department-card .card-body {
        padding: 1.5rem;
    }

    .department-card .card-body p {
        margin: 0.5rem 0;
        color: var(--text-secondary);
        font-size: 0.95rem;
    }

    .department-card .card-body strong {
        color: var(--text-primary);
        font-weight: 500;
    }

    .department-card .card-footer {
        padding: 1.5rem;
        border-top: 1px solid var(--bg-light);
        display: flex;
        gap: 1rem;
        justify-content: flex-end;
    }

    /* Department Action Buttons */
    .btn-success {
        background: var(--gradient-success);
        color: white;
        border: none;
        padding: 0.6rem 1.2rem;
        border-radius: var(--border-radius);
        font-weight: 500;
        transition: var(--transition);
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-success:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(76, 201, 240, 0.3);
    }

    .btn-danger {
        background: var(--gradient-warning);
        color: white;
        border: none;
        padding: 0.6rem 1.2rem;
        border-radius: var(--border-radius);
        font-weight: 500;
        transition: var(--transition);
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        position: relative;
        overflow: hidden;
    }

    .btn-danger::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(45deg, #f72585, #b5179e);
        opacity: 0;
        transition: var(--transition);
    }

    .btn-danger:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(247, 37, 133, 0.3);
    }

    .btn-danger:hover::before {
        opacity: 1;
    }

    .btn-danger span {
        position: relative;
        z-index: 1;
    }

    .btn-danger i {
        position: relative;
        z-index: 1;
    }

    .no-departments {
        text-align: center;
        padding: 3rem;
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--card-shadow);
    }

    .no-departments i {
        font-size: 3rem;
        color: var(--text-secondary);
        margin-bottom: 1rem;
    }

    .no-departments p {
        color: var(--text-secondary);
        margin-bottom: 1.5rem;
    }

    @media (max-width: 768px) {
        .departments-grid {
            grid-template-columns: 1fr;
        }

        .department-card .card-footer {
            flex-direction: column;
        }

        .department-card .card-footer button,
        .department-card .card-footer form {
            width: 100%;
        }

        .btn-danger,
        .btn-success {
            width: 100%;
            justify-content: center;
        }
    }

    /* Election Management Styles */
    .election-form, .link-form {
        max-width: 800px;
        margin: 0 auto;
    }

    .mt-4 {
        margin-top: 2rem;
    }

    .table-responsive {
        overflow-x: auto;
        border-radius: var(--border-radius);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }

    .table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        background: white;
    }

    .table th {
        background: var(--gradient-primary);
        color: white;
        font-weight: 500;
        text-align: left;
        padding: 1rem;
        font-size: 0.95rem;
    }

    .table th:first-child {
        border-top-left-radius: var(--border-radius);
    }

    .table th:last-child {
        border-top-right-radius: var(--border-radius);
    }

    .table td {
        padding: 1rem;
        border-bottom: 1px solid var(--bg-light);
        color: var(--text-primary);
        font-size: 0.95rem;
    }

    .table tr:last-child td {
        border-bottom: none;
    }

    .table tr:hover td {
        background: rgba(67, 97, 238, 0.02);
    }

    /* Election Action Buttons */
    .btn-sm {
        padding: 0.5rem 1rem;
        font-size: 0.9rem;
    }

    .btn-primary {
        background: var(--gradient-primary);
        color: white;
        border: none;
        padding: 0.8rem 1.5rem;
        border-radius: var(--border-radius);
        font-weight: 500;
        transition: var(--transition);
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        position: relative;
        overflow: hidden;
    }

    .btn-primary::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(45deg, var(--secondary-color), var(--primary-color));
        opacity: 0;
        transition: var(--transition);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(67, 97, 238, 0.3);
    }

    .btn-primary:hover::before {
        opacity: 1;
    }

    .btn-primary span, .btn-primary i {
        position: relative;
        z-index: 1;
    }

    /* Form Controls Enhancement */
    .form-control {
        width: 100%;
        padding: 0.8rem 1rem;
        border: 2px solid var(--bg-light);
        border-radius: var(--border-radius);
        font-size: 0.95rem;
        transition: var(--transition);
        background: white;
    }

    .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        outline: none;
    }

    .form-control:hover {
        border-color: var(--primary-color);
    }

    select.form-control {
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%234361ee' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 1rem center;
        background-size: 1rem;
        padding-right: 2.5rem;
    }

    textarea.form-control {
        min-height: 100px;
        resize: vertical;
    }

    /* Card Enhancement for Election Forms */
    .card {
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--card-shadow);
        transition: var(--transition);
        border: none;
        overflow: hidden;
    }

    .card:hover {
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }

    .card-header {
        padding: 1.5rem;
        border-bottom: 1px solid var(--bg-light);
        background: linear-gradient(to right, rgba(67, 97, 238, 0.05), rgba(67, 97, 238, 0.02));
    }

    .card-title {
        color: var(--text-primary);
        font-size: 1.3rem;
        margin: 0;
        font-weight: 600;
    }

    .card-body {
        padding: 1.5rem;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        color: var(--text-primary);
        font-weight: 500;
        font-size: 0.95rem;
    }

    @media (max-width: 768px) {
        .table-responsive {
            margin: 0 -1rem;
            border-radius: 0;
        }

        .table th, .table td {
            padding: 0.8rem;
            font-size: 0.9rem;
        }

        .btn-primary, .btn-danger {
            width: 100%;
            justify-content: center;
        }

        .election-form, .link-form {
            padding: 0 1rem;
        }
    }
</style> 