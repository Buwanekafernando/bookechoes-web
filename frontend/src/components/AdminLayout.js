import React, { useEffect } from 'react';
import { Link, useNavigate, useLocation } from 'react-router-dom';
import '../styles/Admin.css';
import logo from '../assets/logo-be.png';

const AdminLayout = ({ children }) => {
    const navigate = useNavigate();
    const location = useLocation();

    useEffect(() => {
        const token = localStorage.getItem('adminToken');
        if (!token) {
            navigate('/admin/login');
        }
    }, [navigate]);

    const handleLogout = () => {
        localStorage.removeItem('adminToken');
        navigate('/admin/login');
    };

    const menuItems = [
        { path: '/admin/dashboard', label: 'Dashboard' },
        { path: '/admin/authors', label: 'Authors' },
        { path: '/admin/books', label: 'Books' },
        { path: '/admin/ebooks', label: 'Ebooks' },
        { path: '/admin/bookshops', label: 'Bookshops' },
        { path: '/admin/publishers', label: 'Publishers' },
        { path: '/admin/events', label: 'Events' },
    ];

    return (
        <div className="admin-container">
            {/* Sidebar */}
            <aside className="admin-sidebar">
                <div className="admin-logo">
                    <img src={logo} alt="BookEchoes Logo" />
                    <h2>BookEchoes</h2>
                </div>
                <nav className="admin-nav">
                    <ul>
                        {menuItems.map((item) => (
                            <li key={item.path} className={location.pathname === item.path ? 'active' : ''}>
                                <Link to={item.path}>{item.label}</Link>
                            </li>
                        ))}
                    </ul>
                </nav>
                <div className="admin-logout">
                    <button onClick={handleLogout}>Logout</button>
                </div>
            </aside>

            {/* Main Content */}
            <main className="admin-content">
                <header className="admin-header">
                    <h3>Administration Panel</h3>
                    <div className="user-info">Admin User</div>
                </header>
                <div className="admin-page-content">
                    {children}
                </div>
            </main>
        </div>
    );
};

export default AdminLayout;
