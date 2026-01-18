import React, { useState, useEffect } from 'react';
import AdminLayout from '../../components/AdminLayout';

const AdminAuthors = () => {
    const [authors, setAuthors] = useState([]);
    const [loading, setLoading] = useState(true);
    const [showModal, setShowModal] = useState(false);
    const [editingAuthor, setEditingAuthor] = useState(null);

    const [formData, setFormData] = useState({
        name: '',
        country: '',
        no_of_books_published: 0,
        about: '',
        website_url: '',
        socialmedia_url: '',
        image_url: ''
    });

    const API_URL = "http://localhost/backend/api/index.php/authors";

    const getAuthHeaders = () => {
        const token = localStorage.getItem('adminToken');
        return token ? { 'Authorization': `Bearer ${token}` } : {};
    };

    useEffect(() => {
        fetchAuthors();
    }, []);

    const fetchAuthors = async () => {
        try {
            const response = await fetch(API_URL, { headers: getAuthHeaders() });
            const data = await response.json();
            if (data.body) {
                setAuthors(data.body);
            } else {
                setAuthors([]);
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
                await fetch(`${API_URL}/${id}`, {
                    method: 'DELETE',
                    headers: getAuthHeaders()
                });
                fetchAuthors();
            } catch (error) {
                console.error("Error deleting:", error);
            }
        }
    };

    const handleEdit = (author) => {
        setEditingAuthor(author);
        setFormData({
            name: author.name,
            country: author.country || '',
            no_of_books_published: author.no_of_books_published || 0,
            about: author.about || '',
            website_url: author.website_url || '',
            socialmedia_url: author.socialmedia_url || '',
            image_url: author.image_url || ''
        });
        setShowModal(true);
    };

    const handleAddNew = () => {
        setEditingAuthor(null);
        setFormData({ name: '', country: '', no_of_books_published: 0, about: '', website_url: '', socialmedia_url: '', image_url: '' });
        setShowModal(true);
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        const method = editingAuthor ? 'PUT' : 'POST';
        const url = editingAuthor ? `${API_URL}/${editingAuthor.author_id}` : API_URL;

        const res = await fetch(url, {
            method,
            headers: {
                'Content-Type': 'application/json',
                ...getAuthHeaders()
            },
            body: JSON.stringify(formData)
        });

        if (res.ok) {
            setShowModal(false);
            fetchAuthors();
        } else {
            alert("Failed to save author.");
        }
    };

    return (
        <AdminLayout>
            <div className="dashboard-header">
                <div>
                    <h1>Manage Authors</h1>
                    <p style={{ color: 'var(--text-muted)', fontSize: '0.9rem' }}>View and manage book authors in the system.</p>
                </div>
                <button className="btn-primary" onClick={handleAddNew}>+ Add New Author</button>
            </div>

            <div className="table-container">
                {loading ? <p style={{ padding: '20px' }}>Loading...</p> : (
                    <table className="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Books</th>
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
                                        <button className="btn-edit" onClick={() => handleEdit(author)}>Edit</button>
                                        <button className="btn-delete" onClick={() => handleDelete(author.author_id)}>Delete</button>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                )}
            </div>

            {showModal && (
                <div className="modal-overlay">
                    <div className="modal">
                        <div className="modal-header">
                            <h2>{editingAuthor ? 'Edit Author' : 'Add New Author'}</h2>
                            <button className="close-btn" onClick={() => setShowModal(false)}>&times;</button>
                        </div>
                        <form onSubmit={handleSubmit}>
                            <div className="form-group">
                                <label>Name</label>
                                <input type="text" value={formData.name} onChange={e => setFormData({ ...formData, name: e.target.value })} required />
                            </div>
                            <div className="form-group">
                                <label>Country</label>
                                <input type="text" value={formData.country} onChange={e => setFormData({ ...formData, country: e.target.value })} />
                            </div>
                            <div className="form-group">
                                <label>No of Books Published</label>
                                <input type="number" value={formData.no_of_books_published} onChange={e => setFormData({ ...formData, no_of_books_published: e.target.value })} />
                            </div>
                            <div className="form-group">
                                <label>About</label>
                                <textarea value={formData.about} onChange={e => setFormData({ ...formData, about: e.target.value })} rows="3"></textarea>
                            </div>
                            <div className="form-group">
                                <label>Website URL</label>
                                <input type="url" value={formData.website_url} onChange={e => setFormData({ ...formData, website_url: e.target.value })} />
                            </div>
                            <div className="form-group">
                                <label>Social Media URL</label>
                                <input type="url" value={formData.socialmedia_url} onChange={e => setFormData({ ...formData, socialmedia_url: e.target.value })} />
                            </div>
                            <div className="form-group">
                                <label>Image URL</label>
                                <input type="text" value={formData.image_url} onChange={e => setFormData({ ...formData, image_url: e.target.value })} />
                            </div>
                            <div className="modal-footer">
                                <button type="button" className="btn-secondary" onClick={() => setShowModal(false)}>Cancel</button>
                                <button type="submit" className="btn-primary">Save Author</button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </AdminLayout>
    );
};

export default AdminAuthors;
