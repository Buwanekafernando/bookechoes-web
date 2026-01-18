import React, { useState, useEffect } from 'react';
import AdminLayout from '../../components/AdminLayout';

const AdminEbooks = () => {
    const [ebooks, setEbooks] = useState([]);
    const [authors, setAuthors] = useState([]);
    const [loading, setLoading] = useState(true);
    const [showModal, setShowModal] = useState(false);
    const [editingEbook, setEditingEbook] = useState(null);

    const [formData, setFormData] = useState({
        name: '',
        category: '',
        year_of_publish: new Date().getFullYear(),
        language: 'English',
        number_of_chapters: 0,
        image_url: '',
        author_id: ''
    });

    const API_BASE = "http://localhost/backend/api/index.php";

    const getAuthHeaders = () => {
        const token = localStorage.getItem('adminToken');
        return token ? { 'Authorization': `Bearer ${token}` } : {};
    };

    useEffect(() => {
        fetchData();
    }, []);

    const fetchData = async () => {
        try {
            const headers = getAuthHeaders();
            const [ebooksRes, authorsRes] = await Promise.all([
                fetch(`${API_BASE}/ebooks`, { headers }),
                fetch(`${API_BASE}/authors`, { headers })
            ]);
            const ebooksData = await ebooksRes.json();
            const authorsData = await authorsRes.json();
            setEbooks(ebooksData.body || []);
            setAuthors(authorsData.body || []);
        } catch (error) { console.error(error); }
        finally { setLoading(false); }
    };

    const handleDelete = async (id) => {
        if (window.confirm("Delete this ebook?")) {
            await fetch(`${API_BASE}/ebooks/${id}`, {
                method: 'DELETE',
                headers: getAuthHeaders()
            });
            fetchData();
        }
    };

    const handleEdit = (ebook) => {
        setEditingEbook(ebook);
        setFormData({
            name: ebook.name,
            category: ebook.category,
            year_of_publish: ebook.year_of_publish,
            language: ebook.language,
            number_of_chapters: ebook.number_of_chapters || 0,
            image_url: ebook.image_url || '',
            author_id: ebook.author_id
        });
        setShowModal(true);
    };

    const handleAddNew = () => {
        setEditingEbook(null);
        setFormData({ name: '', category: '', year_of_publish: 2024, language: 'English', number_of_chapters: 0, image_url: '', author_id: '' });
        setShowModal(true);
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        const method = editingEbook ? 'PUT' : 'POST';
        const url = editingEbook ? `${API_BASE}/ebooks/${editingEbook.ebook_id}` : `${API_BASE}/ebooks`;

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
            fetchData();
        } else {
            alert("Failed to save ebook.");
        }
    };

    return (
        <AdminLayout>
            <div className="dashboard-header">
                <div>
                    <h1>Manage Ebooks</h1>
                    <p style={{ color: 'var(--text-muted)', fontSize: '0.9rem' }}>Digital catalog and ebook management.</p>
                </div>
                <button className="btn-primary" onClick={handleAddNew}>+ Add New Ebook</button>
            </div>

            <div className="table-container">
                {loading ? <p style={{ padding: '20px' }}>Loading...</p> : (
                    <table className="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Author</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            {ebooks.map(ebook => (
                                <tr key={ebook.ebook_id}>
                                    <td>{ebook.ebook_id}</td>
                                    <td>
                                        <div style={{ display: 'flex', alignItems: 'center', gap: '10px' }}>
                                            {ebook.image_url && <img src={ebook.image_url} alt="" style={{ width: '30px', height: '40px', objectFit: 'cover' }} />}
                                            {ebook.name}
                                        </div>
                                    </td>
                                    <td>{ebook.category}</td>
                                    <td>{ebook.author_name || ebook.author_id}</td>
                                    <td className="actions-cell">
                                        <button className="btn-edit" onClick={() => handleEdit(ebook)}>Edit</button>
                                        <button className="btn-delete" onClick={() => handleDelete(ebook.ebook_id)}>Delete</button>
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
                            <h2>{editingEbook ? 'Edit Ebook' : 'Add New Ebook'}</h2>
                            <button className="close-btn" onClick={() => setShowModal(false)}>&times;</button>
                        </div>
                        <form onSubmit={handleSubmit}>
                            <div className="form-group">
                                <label>Title/Name</label>
                                <input type="text" value={formData.name} onChange={e => setFormData({ ...formData, name: e.target.value })} required />
                            </div>
                            <div className="form-group">
                                <label>Category</label>
                                <input type="text" value={formData.category} onChange={e => setFormData({ ...formData, category: e.target.value })} required />
                            </div>
                            <div style={{ display: 'flex', gap: '20px' }}>
                                <div className="form-group" style={{ flex: 1 }}>
                                    <label>Year</label>
                                    <input type="number" value={formData.year_of_publish} onChange={e => setFormData({ ...formData, year_of_publish: e.target.value })} />
                                </div>
                                <div className="form-group" style={{ flex: 1 }}>
                                    <label>Chapters</label>
                                    <input type="number" value={formData.number_of_chapters} onChange={e => setFormData({ ...formData, number_of_chapters: e.target.value })} />
                                </div>
                            </div>
                            <div className="form-group">
                                <label>Language</label>
                                <input type="text" value={formData.language} onChange={e => setFormData({ ...formData, language: e.target.value })} />
                            </div>
                            <div className="form-group">
                                <label>Author</label>
                                <select value={formData.author_id} onChange={e => setFormData({ ...formData, author_id: e.target.value })} required>
                                    <option value="">Select Author</option>
                                    {authors.map(a => <option key={a.author_id} value={a.author_id}>{a.name}</option>)}
                                </select>
                            </div>
                            <div className="form-group">
                                <label>Image URL</label>
                                <input type="text" value={formData.image_url} onChange={e => setFormData({ ...formData, image_url: e.target.value })} />
                            </div>
                            <div className="modal-footer">
                                <button type="button" className="btn-secondary" onClick={() => setShowModal(false)}>Cancel</button>
                                <button type="submit" className="btn-primary">Save Ebook</button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </AdminLayout>
    );
};

export default AdminEbooks;
