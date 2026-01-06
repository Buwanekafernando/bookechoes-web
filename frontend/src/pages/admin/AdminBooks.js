import React, { useState, useEffect } from 'react';
import AdminLayout from '../../components/AdminLayout';

const AdminBooks = () => {
    const [books, setBooks] = useState([]);
    const [authors, setAuthors] = useState([]);
    const [publishers, setPublishers] = useState([]);
    const [loading, setLoading] = useState(true);
    const [showModal, setShowModal] = useState(false);
    const [editingBook, setEditingBook] = useState(null);

    const [formData, setFormData] = useState({
        title: '',
        category: '',
        year_of_publish: new Date().getFullYear(),
        number_of_chapters: 0,
        language: 'English',
        image_url: '',
        author_id: '',
        publisher_id: ''
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
            const [booksRes, authorsRes, pubsRes] = await Promise.all([
                fetch(`${API_BASE}/books`, { headers }),
                fetch(`${API_BASE}/authors`, { headers }),
                fetch(`${API_BASE}/publishers`, { headers })
            ]);

            const booksData = await booksRes.json();
            const authorsData = await authorsRes.json();
            const pubsData = await pubsRes.json();

            setBooks(booksData.body || []);
            setAuthors(authorsData.body || []);
            setPublishers(pubsData.body || []);
        } catch (error) {
            console.error("Error fetching data:", error);
        } finally {
            setLoading(false);
        }
    };

    const handleDelete = async (id) => {
        if (window.confirm("Are you sure you want to delete this book?")) {
            await fetch(`${API_BASE}/books/${id}`, { 
                method: 'DELETE',
                headers: getAuthHeaders()
            });
            fetchData();
        }
    };

    const handleEdit = (book) => {
        setEditingBook(book);
        setFormData({
            title: book.title,
            category: book.category,
            year_of_publish: book.year_of_publish,
            number_of_chapters: book.number_of_chapters,
            language: book.language,
            image_url: book.image_url || '',
            author_id: book.author_id,
            publisher_id: book.publisher_id
        });
        setShowModal(true);
    };

    const handleAddNew = () => {
        setEditingBook(null);
        setFormData({
            title: '', category: '', year_of_publish: 2024, number_of_chapters: 10,
            language: 'English', image_url: '', author_id: '', publisher_id: ''
        });
        setShowModal(true);
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        const method = editingBook ? 'PUT' : 'POST';
        const url = editingBook ? `${API_BASE}/books/${editingBook.book_id}` : `${API_BASE}/books`;

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
            alert("Failed to save book.");
        }
    };

    return (
        <AdminLayout>
            <div className="dashboard-header">
                <h1>Manage Books</h1>
                <button className="btn-primary" onClick={handleAddNew}>+ Add Book</button>
            </div>

            <div className="table-container">
                {loading ? <p>Loading...</p> : (
                    <table className="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Author</th>
                                <th>Publisher</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            {books.map(book => (
                                <tr key={book.book_id}>
                                    <td>{book.book_id}</td>
                                    <td>
                                        <div style={{ display: 'flex', alignItems: 'center', gap: '10px' }}>
                                            {book.image_url && <img src={book.image_url} alt="" style={{ width: '30px', height: '40px', objectFit: 'cover' }} />}
                                            {book.title}
                                        </div>
                                    </td>
                                    <td>{book.category}</td>
                                    <td>{book.author_name || book.author_id}</td>
                                    <td>{book.publisher_name || book.publisher_id}</td>
                                    <td className="actions-cell">
                                        <button className="btn-edit" onClick={() => handleEdit(book)}>Edit</button>
                                        <button className="btn-delete" onClick={() => handleDelete(book.book_id)}>Delete</button>
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
                            <h2>{editingBook ? 'Edit Book' : 'Add New Book'}</h2>
                            <button className="close-btn" onClick={() => setShowModal(false)}>&times;</button>
                        </div>
                        <form onSubmit={handleSubmit}>
                            <div className="form-group">
                                <label>Title</label>
                                <input type="text" value={formData.title} onChange={e => setFormData({ ...formData, title: e.target.value })} required />
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
                                <label>Publisher</label>
                                <select value={formData.publisher_id} onChange={e => setFormData({ ...formData, publisher_id: e.target.value })} required>
                                    <option value="">Select Publisher</option>
                                    {publishers.map(p => <option key={p.publisher_id} value={p.publisher_id}>{p.name}</option>)}
                                </select>
                            </div>
                            <div className="form-group">
                                <label>Image URL</label>
                                <input type="text" value={formData.image_url} onChange={e => setFormData({ ...formData, image_url: e.target.value })} />
                            </div>
                            <div className="modal-footer">
                                <button type="button" onClick={() => setShowModal(false)} style={{ padding: '10px', background: '#eee', border: 'none' }}>Cancel</button>
                                <button type="submit" className="btn-primary">Save Book</button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </AdminLayout>
    );
};

export default AdminBooks;
