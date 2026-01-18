import React, { useState, useEffect } from 'react';
import AdminLayout from '../../components/AdminLayout';

const AdminDashboard = () => {
    const [stats, setStats] = useState({
        books: 0,
        authors: 0,
        ebooks: 0,
        bookshops: 0,
        publishers: 0,
        events: 0
    });
    const [ingestionMessage, setIngestionMessage] = useState('');

    const getAuthHeaders = () => {
        const token = localStorage.getItem('adminToken');
        return token ? { 'Authorization': `Bearer ${token}` } : {};
    };

    const handleIngestion = async (type) => {
        try {
            setIngestionMessage('Ingesting...');
            const response = await fetch(`http://localhost/backend/api/index.php/ai?action=ingest&type=${type}`, {
                method: 'POST',
                headers: getAuthHeaders()
            });
            const data = await response.json();
            if (response.ok) {
                setIngestionMessage(`Successfully ingested ${data.inserted} ${type}.`);
            } else {
                setIngestionMessage(`Error: ${data.message}`);
            }
        } catch (error) {
            setIngestionMessage('Network error during ingestion.');
        }
    };
    useEffect(() => {
        const fetchStats = async () => {
            try {
                const headers = getAuthHeaders();
                const [booksRes, authorsRes, ebooksRes, shopsRes, pubsRes, eventsRes] = await Promise.all([
                    fetch('http://localhost/backend/api/index.php/books', { headers }),
                    fetch('http://localhost/backend/api/index.php/authors', { headers }),
                    fetch('http://localhost/backend/api/index.php/ebooks', { headers }),
                    fetch('http://localhost/backend/api/index.php/bookshops', { headers }),
                    fetch('http://localhost/backend/api/index.php/publishers', { headers }),
                    fetch('http://localhost/backend/api/index.php/events', { headers })
                ]);

                const books = await booksRes.json();
                const authors = await authorsRes.json();
                const ebooks = await ebooksRes.json();
                const shops = await shopsRes.json();
                const pubs = await pubsRes.json();
                const events = await eventsRes.json();

                setStats({
                    books: books.body ? books.body.length : 0,
                    authors: authors.body ? authors.body.length : 0,
                    ebooks: ebooks.body ? ebooks.body.length : 0,
                    bookshops: shops.body ? shops.body.length : 0,
                    publishers: pubs.body ? pubs.body.length : 0,
                    events: events.body ? events.body.length : 0
                });

            } catch (error) {
                console.error("Failed to fetch dashboard stats", error);
            }
        };

        fetchStats();
    }, []);

    return (
        <AdminLayout>
            <div className="dashboard-header">
                <div>
                    <h1>Dashboard Overview</h1>
                    <p style={{ color: 'var(--text-muted)', marginTop: '5px' }}>Welcome to the BookEchoes Administration Portal.</p>
                </div>
            </div>

            <div className="stats-grid">
                <div className="stat-card books">
                    <div className="stat-label">Total Books</div>
                    <div className="stat-value">{stats.books}</div>
                </div>

                <div className="stat-card authors">
                    <div className="stat-label">Total Authors</div>
                    <div className="stat-value">{stats.authors}</div>
                </div>

                <div className="stat-card ebooks">
                    <div className="stat-label">Total Ebooks</div>
                    <div className="stat-value">{stats.ebooks}</div>
                </div>

                <div className="stat-card shops">
                    <div className="stat-label">Bookshops</div>
                    <div className="stat-value">{stats.bookshops}</div>
                </div>

                <div className="stat-card publishers">
                    <div className="stat-label">Publishers</div>
                    <div className="stat-value">{stats.publishers}</div>
                </div>

                <div className="stat-card events">
                    <div className="stat-label">Events</div>
                    <div className="stat-value">{stats.events}</div>
                </div>
            </div>

            <div className="ingestion-section">
                <h2>AI Data Ingestion</h2>
                <p>Use Gemini AI to fetch and enrich data from the internet.</p>
                <div className="ingestion-actions">
                    <button className="btn-ingest books" onClick={() => handleIngestion('books')}>Ingest Books</button>
                    <button className="btn-ingest authors" onClick={() => handleIngestion('authors')}>Ingest Authors</button>
                    <button className="btn-ingest shops" onClick={() => handleIngestion('bookshops')}>Ingest Bookshops</button>
                    <button className="btn-ingest events" onClick={() => handleIngestion('events')}>Ingest Events</button>
                    <button className="btn-ingest ebooks" onClick={() => handleIngestion('ebooks')}>Ingest Ebooks</button>
                    <button className="btn-ingest publishers" onClick={() => handleIngestion('news')}>Ingest News</button>
                </div>
                {ingestionMessage && (
                    <div className={`ingestion-status ${ingestionMessage.includes('Error') ? 'error' : 'success'}`}>
                        {ingestionMessage}
                    </div>
                )}
            </div>
        </AdminLayout>
    );
};

export default AdminDashboard;
