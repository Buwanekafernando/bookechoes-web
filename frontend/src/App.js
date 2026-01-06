import React from 'react';
import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import Home from './pages/Home';
import Footer from './components/Footer';
import AdminLogin from './pages/admin/AdminLogin';
import AdminDashboard from './pages/admin/AdminDashboard';
import AdminAuthors from './pages/admin/AdminAuthors';
import AdminBooks from './pages/admin/AdminBooks';
import AdminEbooks from './pages/admin/AdminEbooks';
import AdminBookshops from './pages/admin/AdminBookshops';
import './App.css';

const ProtectedRoute = ({ children }) => {
  const token = localStorage.getItem('adminToken');
  return token ? children : <Navigate to="/admin/login" />;
};

function App() {
  return (
    <Router>
      <div className="App">
        <Routes>
          <Route path="/" element={<Home />} />

          {/* Admin Routes */}
          <Route path="/admin/login" element={<AdminLogin />} />
          <Route path="/admin" element={<Navigate to="/admin/login" />} />
          <Route path="/admin/dashboard" element={<ProtectedRoute><AdminDashboard /></ProtectedRoute>} />
          <Route path="/admin/authors" element={<ProtectedRoute><AdminAuthors /></ProtectedRoute>} />
          <Route path="/admin/books" element={<ProtectedRoute><AdminBooks /></ProtectedRoute>} />
          <Route path="/admin/ebooks" element={<ProtectedRoute><AdminEbooks /></ProtectedRoute>} />
          <Route path="/admin/bookshops" element={<ProtectedRoute><AdminBookshops /></ProtectedRoute>} />

        </Routes>
        <Footer />
      </div>
    </Router>
  );
}

export default App;
