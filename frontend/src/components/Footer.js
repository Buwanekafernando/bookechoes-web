import React from 'react';
import logoBe from '../assets/logo-be.png';
import { FaFacebook, FaInstagram } from 'react-icons/fa';
import { motion } from 'framer-motion';
import '../styles/Footer.css';

const Footer = () => {
    return (
        <footer className="footer">
            {/* Top Curve - Concave "Valley" */}
            <div className="wave-container">
                <svg viewBox="0 0 1440 100" className="wave" preserveAspectRatio="none">
                    {/* Matching page bg color to create cutout effect over footer bg */}
                    <path fill="#FFF5E6" d="M0,0 C480,120 960,120 1440,0 V0 H0 Z"></path>
                </svg>
            </div>

            <div className="footer-content">
                {/* Center Logo */}
                <div className="footer-logo-section">
                    <motion.img
                        src={logoBe}
                        alt="BookEchoes Logo"
                        className="footer-logo"
                        initial={{ opacity: 0, y: 20 }}
                        whileInView={{ opacity: 1, y: 0 }}
                        transition={{ duration: 0.8 }}
                    />
                </div>

                <div className="links-container">
                    {/* Column 1: Discover Books */}
                    <div className="footer-column">
                        <h3 className="footer-heading">DISCOVER BOOKS</h3>
                        <ul className="footer-list">
                            <li className="footer-list-item">Fiction</li>
                            <li className="footer-list-item">Thriller and Suspense</li>
                            <li className="footer-list-item">Mystery and Detective</li>
                            <li className="footer-list-item">Romance</li>
                            <li className="footer-list-item">Non Fiction</li>
                            <li className="footer-list-item">Sci-Fi and Fantasy</li>
                            <li className="footer-list-item">Adult</li>
                            <li className="footer-list-item">Psychological</li>
                        </ul>
                    </div>

                    {/* Column 2: News */}
                    <div className="footer-column">
                        <h3 className="footer-heading">NEWS</h3>
                        <ul className="footer-list">
                            <li className="footer-list-item">Bestseller</li>
                            <li className="footer-list-item">New York Times</li>
                            <li className="footer-list-item">Booklist</li>
                            <li className="footer-list-item">Awards</li>
                            <li className="footer-list-item">Book to Screen</li>
                            <li className="footer-list-item">Seen & Heard</li>
                        </ul>
                    </div>

                    {/* Column 3: Authors */}
                    <div className="footer-column">
                        <h3 className="footer-heading">AUTHORS</h3>
                        {/* Space for author links or content */}
                    </div>

                    {/* Column 4: About & Social */}
                    <div className="footer-column">
                        <h3 className="footer-heading">ABOUT BOOKECHOES</h3>
                        <ul className="footer-list">
                            <li className="footer-list-item">About BookEchoes</li>
                            <li className="footer-list-item">Contest</li>
                        </ul>

                        <h3 className="footer-heading" style={{ marginTop: '2rem' }}>VISIT</h3>
                        <div className="social-icons">
                            <motion.div whileHover={{ scale: 1.2 }} className="icon-wrapper">
                                <FaFacebook size={32} color="#1877F2" />
                            </motion.div>
                            <motion.div whileHover={{ scale: 1.2 }} className="icon-wrapper">
                                <FaInstagram size={32} color="#E4405F" />
                            </motion.div>
                        </div>
                    </div>
                </div>
            </div>

            {/* Black bottom line */}
            <div className="bottom-line"></div>
        </footer>
    );
};

export default Footer;
