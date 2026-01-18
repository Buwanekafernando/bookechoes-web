import React, { useState, useEffect } from 'react';
import AdminLayout from '../../components/AdminLayout';

const AdminBookshops = () => {
    const [shops, setShops] = useState([]);
    const [loading, setLoading] = useState(true);
    const [showModal, setShowModal] = useState(false);
    const [editingShop, setEditingShop] = useState(null);
    const [formData, setFormData] = useState({ name: '', location: '', country: '' });

    // Inventory State
    const [showInventory, setShowInventory] = useState(false);
    const [currentShop, setCurrentShop] = useState(null);
    const [inventory, setInventory] = useState([]);
    const [allBooks, setAllBooks] = useState([]);
    const [invFormData, setInvFormData] = useState({ book_id: '', stock_quantity: 0, price: 0 });

    const API_BASE = "http://localhost/backend/api/index.php";

    const getAuthHeaders = () => {
        const token = localStorage.getItem('adminToken');
        return token ? { 'Authorization': `Bearer ${token}` } : {};
    };

    useEffect(() => {
        fetchShops();
        fetchBooks();
    }, []);

    const fetchShops = async () => {
        try {
            const res = await fetch(`${API_BASE}/bookshops`, { headers: getAuthHeaders() });
            const data = await res.json();
            setShops(data.body || []);
        } catch (error) { console.error(error); }
        finally { setLoading(false); }
    };

    const fetchBooks = async () => {
        try {
            const res = await fetch(`${API_BASE}/books`, { headers: getAuthHeaders() });
            const data = await res.json();
            setAllBooks(data.body || []);
        } catch (error) { console.error(error); }
    };

    // Shop CRUD
    const handleShopSubmit = async (e) => {
        e.preventDefault();
        const method = editingShop ? 'PUT' : 'POST';
        const url = editingShop ? `${API_BASE}/bookshops/${editingShop.bookshop_id}` : `${API_BASE}/bookshops`;
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
            fetchShops();
        } else {
            alert("Failed to save shop.");
        }
    };

    const handleDeleteShop = async (id) => {
        if (window.confirm("Delete Shop?")) {
            await fetch(`${API_BASE}/bookshops/${id}`, {
                method: 'DELETE',
                headers: getAuthHeaders()
            });
            fetchShops();
        }
    };

    // Inventory Logic
    const openInventory = async (shop) => {
        setCurrentShop(shop);
        setShowInventory(true);
        fetchInventory(shop.bookshop_id);
    };

    const fetchInventory = async (shopId) => {
        try {
            const res = await fetch(`${API_BASE}/bookshops/${shopId}/inventory`, { headers: getAuthHeaders() });
            const data = await res.json();
            setInventory(data.body || []);
        } catch (error) { console.error(error); }
    };

    const handleAddInventory = async (e) => {
        e.preventDefault();
        const res = await fetch(`${API_BASE}/bookshops/${currentShop.bookshop_id}/inventory`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                ...getAuthHeaders()
            },
            body: JSON.stringify(invFormData)
        });
        if (res.ok) {
            fetchInventory(currentShop.bookshop_id);
            setInvFormData({ book_id: '', stock_quantity: 0, price: 0 });
        } else {
            alert("Failed to add inventory.");
        }
    };

    const handleRemoveInventory = async (bookId) => {
        if (window.confirm("Remove this book from inventory?")) {
            const res = await fetch(`${API_BASE}/bookshops/${currentShop.bookshop_id}/inventory/${bookId}`, {
                method: 'DELETE',
                headers: getAuthHeaders()
            });
            if (res.ok) {
                fetchInventory(currentShop.bookshop_id);
            } else {
                alert("Failed to remove inventory.");
            }
        }
    };

    return (
        <AdminLayout>
            <div className="dashboard-header">
                <div>
                    <h1>Manage Bookshops</h1>
                    <p style={{ color: 'var(--text-muted)', fontSize: '0.9rem' }}>Manage physical bookshop locations and their inventory.</p>
                </div>
                <button className="btn-primary" onClick={() => { setEditingShop(null); setFormData({ name: '', location: '', country: '' }); setShowModal(true); }}>+ Add New Shop</button>
            </div>

            <div className="table-container">
                {loading ? <p style={{ padding: '20px' }}>Loading...</p> : (
                    <table className="data-table">
                        <thead><tr><th>ID</th><th>Name</th><th>Location</th><th>Actions</th></tr></thead>
                        <tbody>
                            {shops.map(shop => (
                                <tr key={shop.bookshop_id}>
                                    <td>{shop.bookshop_id}</td>
                                    <td>{shop.name}</td>
                                    <td>{shop.location}, {shop.country}</td>
                                    <td className="actions-cell">
                                        <button className="btn-edit" onClick={() => { setEditingShop(shop); setFormData({ name: shop.name, location: shop.location, country: shop.country }); setShowModal(true); }}>Edit</button>
                                        <button className="btn-primary" style={{ backgroundColor: '#2d3748', marginLeft: '5px' }} onClick={() => openInventory(shop)}>Manage Stock</button>
                                        <button className="btn-delete" onClick={() => handleDeleteShop(shop.bookshop_id)}>Delete</button>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                )}
            </div>

            {/* Shop Modal */}
            {showModal && (
                <div className="modal-overlay">
                    <div className="modal">
                        <div className="modal-header">
                            <h2>{editingShop ? 'Edit Shop' : 'Add New Shop'}</h2>
                            <button className="close-btn" onClick={() => setShowModal(false)}>&times;</button>
                        </div>
                        <form onSubmit={handleShopSubmit}>
                            <div className="form-group"><label>Name</label><input value={formData.name} onChange={e => setFormData({ ...formData, name: e.target.value })} required /></div>
                            <div className="form-group"><label>Location</label><input value={formData.location} onChange={e => setFormData({ ...formData, location: e.target.value })} required /></div>
                            <div className="form-group"><label>Country</label><input value={formData.country} onChange={e => setFormData({ ...formData, country: e.target.value })} required /></div>
                            <div className="modal-footer">
                                <button type="button" className="btn-secondary" onClick={() => setShowModal(false)}>Cancel</button>
                                <button type="submit" className="btn-primary">Save Shop</button>
                            </div>
                        </form>
                    </div>
                </div>
            )}

            {/* Inventory Modal */}
            {showInventory && currentShop && (
                <div className="modal-overlay">
                    <div className="modal" style={{ maxWidth: '800px' }}>
                        <div className="modal-header">
                            <h2>Inventory for {currentShop.name}</h2>
                            <button className="close-btn" onClick={() => setShowInventory(false)}>&times;</button>
                        </div>

                        <div style={{ marginBottom: '30px', padding: '25px', background: 'var(--bg-light)', borderRadius: '12px', border: '1px solid #edf2f7' }}>
                            <h4 style={{ marginBottom: '15px', fontFamily: 'Playfair Display, serif', color: 'var(--primary-color)' }}>Add New Book to Stock</h4>
                            <form onSubmit={handleAddInventory} style={{ display: 'flex', gap: '15px', alignItems: 'flex-end' }}>
                                <div className="form-group" style={{ flex: 2, marginBottom: 0 }}>
                                    <label>Book</label>
                                    <select value={invFormData.book_id} onChange={e => setInvFormData({ ...invFormData, book_id: e.target.value })} required>
                                        <option value="">Select Book</option>
                                        {allBooks.map(b => <option key={b.book_id} value={b.book_id}>{b.title}</option>)}
                                    </select>
                                </div>
                                <div className="form-group" style={{ flex: 1, marginBottom: 0 }}>
                                    <label>Quantity</label>
                                    <input type="number" value={invFormData.stock_quantity} onChange={e => setInvFormData({ ...invFormData, stock_quantity: e.target.value })} required />
                                </div>
                                <div className="form-group" style={{ flex: 1, marginBottom: 0 }}>
                                    <label>Price</label>
                                    <input type="number" step="0.01" value={invFormData.price} onChange={e => setInvFormData({ ...invFormData, price: e.target.value })} required />
                                </div>
                                <button type="submit" className="btn-primary">Add to Stock</button>
                            </form>
                        </div>

                        <div style={{ maxHeight: '400px', overflowY: 'auto' }}>
                            <table className="data-table">
                                <thead style={{ position: 'sticky', top: 0, background: 'white' }}><tr><th>Book Title</th><th>Stock</th><th>Price</th><th>Action</th></tr></thead>
                                <tbody>
                                    {inventory.map((item, idx) => (
                                        <tr key={idx}>
                                            <td>{item.title}</td>
                                            <td>{item.stock_quantity}</td>
                                            <td>${item.price}</td>
                                            <td>
                                                <button className="btn-delete" onClick={() => handleRemoveInventory(item.book_id)}>Remove</button>
                                            </td>
                                        </tr>
                                    ))}
                                    {inventory.length === 0 && <tr><td colSpan="4" style={{ textAlign: 'center', padding: '20px' }}>No inventory found.</td></tr>}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            )}
        </AdminLayout>
    );
};

export default AdminBookshops;
