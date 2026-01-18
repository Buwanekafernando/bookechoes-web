import React, { useEffect } from 'react';
import { Link, useNavigate, useLocation } from 'react-router-dom';
import { motion, AnimatePresence } from 'framer-motion';
import { FaTachometerAlt, FaUsers, FaBook, FaBookOpen, FaStore, FaBuilding, FaCalendarAlt, FaSignOutAlt } from 'react-icons/fa';
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
        { path: '/admin/dashboard', label: 'Dashboard', icon: <FaTachometerAlt /> },
        { path: '/admin/authors', label: 'Authors', icon: <FaUsers /> },
        { path: '/admin/books', label: 'Books', icon: <FaBook /> },
        { path: '/admin/ebooks', label: 'Ebooks', icon: <FaBookOpen /> },
        { path: '/admin/bookshops', label: 'Bookshops', icon: <FaStore /> },
        { path: '/admin/publishers', label: 'Publishers', icon: <FaBuilding /> },
        { path: '/admin/events', label: 'Events', icon: <FaCalendarAlt /> },
    ];

    return (
        <div className="admin-container">
            {/* Sidebar */}
            <motion.aside
                className="admin-sidebar"
                initial={{ x: -260 }}
                animate={{ x: 0 }}
                transition={{ type: 'spring', damping: 20, stiffness: 100 }}
            >
                <div className="admin-logo">
                    <motion.img
                        src={logo}
                        alt="BookEchoes Logo"
                        initial={{ scale: 0 }}
                        animate={{ scale: 1 }}
                        transition={{ delay: 0.2, type: 'spring' }}
                    />
                    <motion.h2
                        initial={{ opacity: 0 }}
                        animate={{ opacity: 1 }}
                        transition={{ delay: 0.4 }}
                    >
                        BookEchoes
                    </motion.h2>
                </div>
                <nav className="admin-nav">
                    <ul>
                        {menuItems.map((item, index) => (
                            <motion.li
                                key={item.path}
                                className={location.pathname === item.path ? 'active' : ''}
                                initial={{ opacity: 0, x: -20 }}
                                animate={{ opacity: 1, x: 0 }}
                                transition={{ delay: 0.1 * index + 0.3 }}
                            >
                                <Link to={item.path}>
                                    <span className="nav-icon">{item.icon}</span>
                                    {item.label}
                                </Link>
                            </motion.li>
                        ))}
                    </ul>
                </nav>
                <div className="admin-logout">
                    <button onClick={handleLogout}>
                        <FaSignOutAlt style={{ marginRight: '10px' }} />
                        Logout
                    </button>
                </div>
            </motion.aside>

            {/* Main Content */}
            <main className="admin-content">
                <header className="admin-header">
                    <motion.h3
                        initial={{ opacity: 0, y: -10 }}
                        animate={{ opacity: 1, y: 0 }}
                    >
                        Administration Panel
                    </motion.h3>
                    <div className="user-info">
                        <span className="user-badge">Admin User</span>
                    </div>
                </header>
                <div className="admin-page-content">
                    <AnimatePresence mode="wait">
                        <motion.div
                            key={location.pathname}
                            initial={{ opacity: 0, y: 20 }}
                            animate={{ opacity: 1, y: 0 }}
                            exit={{ opacity: 0, y: -20 }}
                            transition={{ duration: 0.3 }}
                        >
                            {children}
                        </motion.div>
                    </AnimatePresence>
                </div>
            </main>
        </div>
    );
};

export default AdminLayout;
