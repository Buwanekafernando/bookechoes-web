import React, { useState, useEffect } from 'react';
import AdminLayout from '../../components/AdminLayout';

const AdminDashboard = () => {
    const [stats, setStats] = useState({
        books: 0,
        authors: 0,
        ebooks: 0,
        bookshops: 0
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
                // We'll fetch all listing endpoints to get counts. 
                // For a production app, we'd want a specific /stats endpoint to avoid data transfer overhead.
                const [booksRes, authorsRes, ebooksRes, shopsRes] = await Promise.all([
                    fetch('http://localhost/backend/api/index.php/books', { headers }),
                    fetch('http://localhost/backend/api/index.php/authors', { headers }),
                    fetch('http://localhost/backend/api/index.php/ebooks', { headers }),
                    fetch('http://localhost/backend/api/index.php/bookshops', { headers })
                ]);

                const books = await booksRes.json();
                const authors = await authorsRes.json();
                const ebooks = await ebooksRes.json();
                const shops = await shopsRes.json();

                setStats({
                    books: books.body ? books.body.length : 0,
                    authors: authors.body ? authors.body.length : 0,
                    ebooks: ebooks.body ? ebooks.body.length : 0,
                    bookshops: shops.body ? shops.body.length : 0
                });

            } catch (error) {
                console.error("Failed to fetch dashboard stats", error);
            }
        };

        fetchStats();
    }, []);

    return (
        <AdminLayout>
            <h1>Dashboard Overview</h1>
            <p>Welcome to the BookEchoes Administration Panel.</p>

            <div className="grid" style={{ gridTemplateColumns: 'repeat(auto-fit, minmax(240px, 1fr))', gap: '20px', marginTop: '30px' }}>
                <div className="card" style={{ padding: '25px', background: 'white', borderLeft: '5px solid #2b6cb0', boxShadow: '0 4px 6px rgba(0,0,0,0.1)' }}>
                    <h3 style={{ margin: '0 0 10px 0', fontSize: '1.2rem', color: '#4a5568' }}>Total Books</h3>
                    <p style={{ fontSize: '2.5rem', fontWeight: 'bold', margin: 0, color: '#2d3748' }}>{stats.books}</p>
                </div>

                <div className="card" style={{ padding: '25px', background: 'white', borderLeft: '5px solid #4A5D23', boxShadow: '0 4px 6px rgba(0,0,0,0.1)' }}>
                    <h3 style={{ margin: '0 0 10px 0', fontSize: '1.2rem', color: '#4a5568' }}>Total Authors</h3>
                    <p style={{ fontSize: '2.5rem', fontWeight: 'bold', margin: 0, color: '#2d3748' }}>{stats.authors}</p>
                </div>

                <div className="card" style={{ padding: '25px', background: 'white', borderLeft: '5px solid #d69e2e', boxShadow: '0 4px 6px rgba(0,0,0,0.1)' }}>
                    <h3 style={{ margin: '0 0 10px 0', fontSize: '1.2rem', color: '#4a5568' }}>Total Ebooks</h3>
                    <p style={{ fontSize: '2.5rem', fontWeight: 'bold', margin: 0, color: '#2d3748' }}>{stats.ebooks}</p>
                </div>

                <div className="card" style={{ padding: '25px', background: 'white', borderLeft: '5px solid #e53e3e', boxShadow: '0 4px 6px rgba(0,0,0,0.1)' }}>
                    <h3 style={{ margin: '0 0 10px 0', fontSize: '1.2rem', color: '#4a5568' }}>Bookshops</h3>
                    <p style={{ fontSize: '2.5rem', fontWeight: 'bold', margin: 0, color: '#2d3748' }}>{stats.bookshops}</p>
                </div>
            </div>

            <div style={{ marginTop: '40px' }}>
                <h2>AI Data Ingestion</h2>
                <p>Use Gemini AI to fetch and enrich data from the internet.</p>
                <div style={{ display: 'flex', gap: '10px', flexWrap: 'wrap' }}>
                    <button onClick={() => handleIngestion('books')} style={{ padding: '10px 20px', background: '#2b6cb0', color: 'white', border: 'none', borderRadius: '5px' }}>Ingest Books</button>
                    <button onClick={() => handleIngestion('authors')} style={{ padding: '10px 20px', background: '#4A5D23', color: 'white', border: 'none', borderRadius: '5px' }}>Ingest Authors</button>
                    <button onClick={() => handleIngestion('bookshops')} style={{ padding: '10px 20px', background: '#d69e2e', color: 'white', border: 'none', borderRadius: '5px' }}>Ingest Bookshops</button>
                    <button onClick={() => handleIngestion('events')} style={{ padding: '10px 20px', background: '#e53e3e', color: 'white', border: 'none', borderRadius: '5px' }}>Ingest Events</button>
                    <button onClick={() => handleIngestion('ebooks')} style={{ padding: '10px 20px', background: '#805ad5', color: 'white', border: 'none', borderRadius: '5px' }}>Ingest Ebooks</button>
                    <button onClick={() => handleIngestion('news')} style={{ padding: '10px 20px', background: '#38b2ac', color: 'white', border: 'none', borderRadius: '5px' }}>Ingest News</button>
                </div>
                {ingestionMessage && <p style={{ marginTop: '10px', color: 'green' }}>{ingestionMessage}</p>}
            </div>
        </AdminLayout>
    );
};

export default AdminDashboard;
