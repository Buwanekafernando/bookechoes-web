import React, { useState, useEffect } from 'react';
import AdminLayout from '../../components/AdminLayout';

const AdminEvents = () => {
    const [events, setEvents] = useState([]);
    const [loading, setLoading] = useState(true);
    const [showModal, setShowModal] = useState(false);
    const [editingEvent, setEditingEvent] = useState(null);

    const [formData, setFormData] = useState({
        name: '',
        location: '',
        description: '',
        date_start: '',
        date_end: '',
        image_url: ''
    });

    const API_URL = "http://localhost/backend/api/index.php/events";

    const getAuthHeaders = () => {
        const token = localStorage.getItem('adminToken');
        return token ? { 'Authorization': `Bearer ${token}` } : {};
    };

    useEffect(() => {
        fetchEvents();
    }, []);

    const fetchEvents = async () => {
        try {
            const response = await fetch(API_URL, { headers: getAuthHeaders() });
            const data = await response.json();
            if (data.body) {
                setEvents(data.body);
            } else {
                setEvents([]);
            }
        } catch (error) {
            console.error("Error fetching events:", error);
        } finally {
            setLoading(false);
        }
    };

    const handleDelete = async (id) => {
        if (window.confirm("Are you sure you want to delete this event?")) {
            try {
                const res = await fetch(`${API_URL}/${id}`, {
                    method: 'DELETE',
                    headers: getAuthHeaders()
                });
                if (res.ok) fetchEvents();
            } catch (error) {
                console.error("Error deleting:", error);
            }
        }
    };

    const handleEdit = (event) => {
        setEditingEvent(event);
        setFormData({
            name: event.name,
            location: event.location,
            description: event.description || '',
            date_start: event.date_start ? event.date_start.split(' ')[0] : '', // Format for date input
            date_end: event.date_end ? event.date_end.split(' ')[0] : '',
            image_url: event.image_url || ''
        });
        setShowModal(true);
    };

    const handleAddNew = () => {
        setEditingEvent(null);
        setFormData({ name: '', location: '', description: '', date_start: '', date_end: '', image_url: '' });
        setShowModal(true);
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        const method = editingEvent ? 'PUT' : 'POST';
        const url = editingEvent ? `${API_URL}/${editingEvent.book_event_id}` : API_URL;

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
            fetchEvents();
        } else {
            const err = await res.json();
            alert("Failed to save event: " + (err.message || "Unknown error"));
        }
    };

    return (
        <AdminLayout>
            <div className="dashboard-header">
                <h1>Manage Events</h1>
                <button className="btn-primary" onClick={handleAddNew}>+ Add Event</button>
            </div>

            <div className="table-container">
                {loading ? <p style={{ padding: '20px' }}>Loading...</p> : (
                    <table className="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Location</th>
                                <th>Start Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            {events.map(event => (
                                <tr key={event.book_event_id}>
                                    <td>{event.book_event_id}</td>
                                    <td>
                                        <div style={{ display: 'flex', alignItems: 'center', gap: '10px' }}>
                                            {event.image_url && <img src={event.image_url} alt="" style={{ width: '40px', height: '25px', objectFit: 'cover' }} />}
                                            {event.name}
                                        </div>
                                    </td>
                                    <td>{event.location}</td>
                                    <td>{event.date_start}</td>
                                    <td className="actions-cell">
                                        <button className="btn-edit" onClick={() => handleEdit(event)}>Edit</button>
                                        <button className="btn-delete" onClick={() => handleDelete(event.book_event_id)}>Delete</button>
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
                            <h2>{editingEvent ? 'Edit Event' : 'Add New Event'}</h2>
                            <button className="close-btn" onClick={() => setShowModal(false)}>&times;</button>
                        </div>
                        <form onSubmit={handleSubmit}>
                            <div className="form-group">
                                <label>Event Name</label>
                                <input type="text" value={formData.name} onChange={e => setFormData({ ...formData, name: e.target.value })} required />
                            </div>
                            <div className="form-group">
                                <label>Location</label>
                                <input type="text" value={formData.location} onChange={e => setFormData({ ...formData, location: e.target.value })} required />
                            </div>
                            <div style={{ display: 'flex', gap: '20px' }}>
                                <div className="form-group" style={{ flex: 1 }}>
                                    <label>Start Date</label>
                                    <input type="date" value={formData.date_start} onChange={e => setFormData({ ...formData, date_start: e.target.value })} required />
                                </div>
                                <div className="form-group" style={{ flex: 1 }}>
                                    <label>End Date</label>
                                    <input type="date" value={formData.date_end} onChange={e => setFormData({ ...formData, date_end: e.target.value })} />
                                </div>
                            </div>
                            <div className="form-group">
                                <label>Description</label>
                                <textarea value={formData.description} onChange={e => setFormData({ ...formData, description: e.target.value })} rows="3"></textarea>
                            </div>
                            <div className="form-group">
                                <label>Image URL</label>
                                <input type="text" value={formData.image_url} onChange={e => setFormData({ ...formData, image_url: e.target.value })} />
                            </div>
                            <div className="modal-footer">
                                <button type="button" onClick={() => setShowModal(false)} style={{ padding: '10px', background: '#eee', border: 'none' }}>Cancel</button>
                                <button type="submit" className="btn-primary">Save Event</button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </AdminLayout>
    );
};

export default AdminEvents;
