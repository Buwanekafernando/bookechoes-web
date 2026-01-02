import React, { useState, useEffect } from 'react';
import AdminLayout from '../../components/AdminLayout';

const AdminDashboard = () => {
    const [stats, setStats] = useState({
        books: 0,
        authors: 0,
        ebooks: 0,
        bookshops: 0
    });

    useEffect(() => {
        const fetchStats = async () => {
            try {
                // We'll fetch all listing endpoints to get counts. 
                // For a production app, we'd want a specific /stats endpoint to avoid data transfer overhead.
                const [booksRes, authorsRes, ebooksRes, shopsRes] = await Promise.all([
                    fetch('http://localhost/backend/api/index.php/books'),
                    fetch('http://localhost/backend/api/index.php/authors'),
                    fetch('http://localhost/backend/api/index.php/ebooks'),
                    fetch('http://localhost/backend/api/index.php/bookshops')
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
        </AdminLayout>
    );
};

export default AdminDashboard;
