import React, { useState, useEffect } from 'react';
import AdminLayout from '../../components/AdminLayout';

const AdminAuthors = () => {
    const [authors, setAuthors] = useState([]);
    const [loading, setLoading] = useState(true);

    // Should be from env or constant
    const API_URL = "http://localhost/backend/api/index.php/authors";

    useEffect(() => {
        fetchAuthors();
    }, []);

    const fetchAuthors = async () => {
        try {
            const response = await fetch(API_URL);
            const data = await response.json();
            if (data.body) {
                setAuthors(data.body);
            }
        } catch (error) {
            console.error("Error fetching authors:", error);
        } finally {
            setLoading(false);
        }
    };

    const handleDelete = async (id) => {
        if (window.confirm("Are you sure you want to delete this author?")) {
            try {
                await fetch(`${API_URL}/${id}`, { method: 'DELETE' });
                fetchAuthors(); // Refresh
            } catch (error) {
                console.error("Error deleting:", error);
            }
        }
    };

    return (
        <AdminLayout>
            <div className="dashboard-header">
                <h1>Manage Authors</h1>
                <button className="btn-primary" onClick={() => alert("Create Modal coming next!")}>+ Add Author</button>
            </div>

            <div className="table-container">
                {loading ? (
                    <p style={{ padding: '20px' }}>Loading...</p>
                ) : (
                    <table className="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>books Published</th>
                                <th>Country</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            {authors.map((author) => (
                                <tr key={author.author_id}>
                                    <td>{author.author_id}</td>
                                    <td>
                                        <div style={{ display: 'flex', alignItems: 'center', gap: '10px' }}>
                                            {author.image_url && <img src={author.image_url} alt="" style={{ width: '30px', height: '30px', borderRadius: '50%', objectFit: 'cover' }} />}
                                            {author.name}
                                        </div>
                                    </td>
                                    <td>{author.no_of_books_published}</td>
                                    <td>{author.country}</td>
                                    <td className="actions-cell">
                                        <button className="btn-edit" onClick={() => alert("Edit " + author.name)}>Edit</button>
                                        <button className="btn-delete" onClick={() => handleDelete(author.author_id)}>Delete</button>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                )}
            </div>
        </AdminLayout>
    );
};

export default AdminAuthors;
