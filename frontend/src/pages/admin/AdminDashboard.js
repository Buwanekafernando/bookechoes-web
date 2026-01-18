import React, { useState, useEffect } from 'react';
import { motion } from 'framer-motion';
import AdminLayout from '../../components/AdminLayout';
import '../../styles/Admin.css';

const AdminDashboard = () => {
    const [stats, setStats] = useState({
        books: 0,
        authors: 0,
        ebooks: 0,
        bookshops: 0,
        publishers: 0,
        events: 0
    });
    const [loading, setLoading] = useState(true);
    const [ingestionStatus, setIngestionStatus] = useState(null);

    const getAuthHeaders = () => {
        const token = localStorage.getItem('adminToken');
        return token ? { 'Authorization': `Bearer ${token}` } : {};
    };

    const fetchStats = async () => {
        try {
            const headers = getAuthHeaders();
            const endpoints = ['books', 'authors', 'ebooks', 'bookshops', 'publishers', 'events'];
            const results = await Promise.all(
                endpoints.map(ep => fetch(`http://localhost/backend/api/index.php/${ep}`, { headers }).then(res => res.json()))
            );

            const newStats = {};
            endpoints.forEach((ep, i) => {
                newStats[ep] = results[i].body ? results[i].body.length : 0;
            });
            setStats(newStats);
        } catch (error) {
            console.error("Error fetching stats:", error);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchStats();
    }, []);

    const handleIngestion = async (type) => {
        setIngestionStatus({ type: 'loading', message: `Contacting AI for ${type} data...` });
        try {
            const response = await fetch(`http://localhost/backend/api/index.php/ai?action=ingest&type=${type}`, {
                method: 'POST',
                headers: getAuthHeaders()
            });
            const data = await response.json();
            if (response.ok) {
                setIngestionStatus({ type: 'success', message: `Successfully ingested ${data.inserted} ${type}.` });
                fetchStats();
            } else {
                setIngestionStatus({ type: 'error', message: data.message });
            }
        } catch (error) {
            setIngestionStatus({ type: 'error', message: "Failed to connect to AI service." });
        }
    };

    const statCards = [
        { label: 'Total Books', value: stats.books, type: 'books' },
        { label: 'Total Authors', value: stats.authors, type: 'authors' },
        { label: 'E-Books', value: stats.ebooks, type: 'ebooks' },
        { label: 'Bookshops', value: stats.bookshops, type: 'shops' },
        { label: 'Publishers', value: stats.publishers, type: 'publishers' },
        { label: 'Active Events', value: stats.events, type: 'events' },
    ];

    return (
        <AdminLayout>
            <div className="dashboard-header">
                <div>
                    <h1>Dashboard</h1>
                    <p style={{ color: 'var(--text-muted)', fontWeight: '600' }}>Welcome back to the BookEchoes control center.</p>
                </div>
            </div>

            <div className="stats-grid">
                {statCards.map((card, index) => (
                    <motion.div
                        key={card.type}
                        className={`stat-card ${card.type}`}
                        initial={{ opacity: 0, y: 20 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ delay: index * 0.1 }}
                        whileHover={{ y: -5, boxShadow: '0 10px 30px rgba(0,0,0,0.1)' }}
                    >
                        <div className="stat-label">{card.label}</div>
                        <div className="stat-value">{loading ? '...' : card.value}</div>
                    </motion.div>
                ))}
            </div>

            <motion.div
                className="ingestion-section"
                initial={{ opacity: 0, scale: 0.95 }}
                animate={{ opacity: 1, scale: 1 }}
                transition={{ delay: 0.6 }}
            >
                <h2>AI Data Enrichment</h2>
                <p>Automate your catalog expansion by fetching verified literary data from the web using Gemini AI.</p>

                <div className="ingestion-actions">
                    {['books', 'authors', 'bookshops', 'events', 'ebooks', 'news'].map(type => (
                        <button
                            key={type}
                            className={`btn-ingest ${type}`}
                            onClick={() => handleIngestion(type)}
                            disabled={ingestionStatus?.type === 'loading'}
                        >
                            Ingest {type.charAt(0).toUpperCase() + type.slice(1)}
                        </button>
                    ))}
                </div>

                {ingestionStatus && (
                    <div className={`ingestion-status ${ingestionStatus.type}`}>
                        {ingestionStatus.message}
                    </div>
                )}
            </motion.div>
        </AdminLayout>
    );
};

export default AdminDashboard;
