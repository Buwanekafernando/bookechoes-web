import React, { useState, useEffect } from 'react';
import AdminLayout from '../../components/AdminLayout';

const AdminPublishers = () => {
    const [publishers, setPublishers] = useState([]);
    const [loading, setLoading] = useState(true);
    const [showModal, setShowModal] = useState(false);
    const [editingPublisher, setEditingPublisher] = useState(null);

    const [formData, setFormData] = useState({
        name: '',
        country: '',
        website_url: ''
    });

    const API_URL = "http://localhost/backend/api/index.php/publishers";

    const getAuthHeaders = () => {
        const token = localStorage.getItem('adminToken');
        return token ? { 'Authorization': `Bearer ${token}` } : {};
    };

    useEffect(() => {
        fetchPublishers();
    }, []);

    const fetchPublishers = async () => {
        try {
            const response = await fetch(API_URL, { headers: getAuthHeaders() });
            const data = await response.json();
            if (data.body) {
                setPublishers(data.body);
            } else {
                setPublishers([]);
            }
        } catch (error) {
            console.error("Error fetching publishers:", error);
        } finally {
            setLoading(false);
        }
    };

    const handleDelete = async (id) => {
        if (window.confirm("Are you sure you want to delete this publisher?")) {
            try {
                const res = await fetch(`${API_URL}/${id}`, {
                    method: 'DELETE',
                    headers: getAuthHeaders()
                });
                if (res.ok) fetchPublishers();
            } catch (error) {
                console.error("Error deleting:", error);
            }
        }
    };

    const handleEdit = (publisher) => {
        setEditingPublisher(publisher);
        setFormData({
            name: publisher.name,
            country: publisher.country || '',
            website_url: publisher.website_url || ''
        });
        setShowModal(true);
    };

    const handleAddNew = () => {
        setEditingPublisher(null);
        setFormData({ name: '', country: '', website_url: '' });
        setShowModal(true);
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        const method = editingPublisher ? 'PUT' : 'POST';
        const url = editingPublisher ? `${API_URL}/${editingPublisher.publisher_id}` : API_URL;

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
            fetchPublishers();
        } else {
            alert("Failed to save publisher.");
        }
    };

    return (
        <AdminLayout>
            <div className="dashboard-header">
                <h1>Manage Publishers</h1>
                <button className="btn-primary" onClick={handleAddNew}>+ Add Publisher</button>
            </div>

            <div className="table-container">
                {loading ? <p style={{ padding: '20px' }}>Loading...</p> : (
                    <table className="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Country</th>
                                <th>Website</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            {publishers.map(pub => (
                                <tr key={pub.publisher_id}>
                                    <td>{pub.publisher_id}</td>
                                    <td>{pub.name}</td>
                                    <td>{pub.country}</td>
                                    <td>
                                        {pub.website_url && <a href={pub.website_url} target="_blank" rel="noopener noreferrer">Visit</a>}
                                    </td>
                                    <td className="actions-cell">
                                        <button className="btn-edit" onClick={() => handleEdit(pub)}>Edit</button>
                                        <button className="btn-delete" onClick={() => handleDelete(pub.publisher_id)}>Delete</button>
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
                            <h2>{editingPublisher ? 'Edit Publisher' : 'Add New Publisher'}</h2>
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
                                <label>Website URL</label>
                                <input type="url" value={formData.website_url} onChange={e => setFormData({ ...formData, website_url: e.target.value })} />
                            </div>
                            <div className="modal-footer">
                                <button type="button" onClick={() => setShowModal(false)} style={{ padding: '10px', background: '#eee', border: 'none' }}>Cancel</button>
                                <button type="submit" className="btn-primary">Save Publisher</button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </AdminLayout>
    );
};

export default AdminPublishers;
