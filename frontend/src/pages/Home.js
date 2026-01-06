import React, { useState } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { FaArrowRight, FaArrowDown } from 'react-icons/fa';
import { Link } from 'react-router-dom';
import '../styles/Home.css';

const Home = () => {
    // Carousel State for "More Fiction" and "Non Fiction"
    const [fictionIndex, setFictionIndex] = useState(0);
    const [nonFictionIndex, setNonFictionIndex] = useState(0);

    // Placeholder items - 10 items to allow for paging
    const items = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
    const itemsPerPage = 5;

    const handleNext = (currentIndex, setIndex) => {
        if (currentIndex + itemsPerPage < items.length) {
            setIndex(currentIndex + 1);
            const nextIndex = currentIndex + itemsPerPage;
            if (nextIndex < items.length) {
                setIndex(nextIndex);
            } else {
                setIndex(0);
            }
        } else {
            setIndex(0);
        }
    };

    const getVisibleItems = (currentIndex) => {
        let visible = items.slice(currentIndex, currentIndex + itemsPerPage);
        return visible;
    };

    return (
        <div className="home-container">
            {/* 1. HERO SECTION */}
            <section className="hero-wrapper">
                <div style={{ display: 'none' }}>
                    {/* Hidden content kept for structure if needed later, but styles set to none */}
                </div>

                {/* Hero Title Overlay */}
                <div className="hero-title-container">
                    <motion.h1
                        className="hero-title"
                        initial={{ opacity: 0, scale: 0.9 }}
                        animate={{ opacity: 1, scale: 1 }}
                        transition={{ duration: 1, delay: 0.3 }}
                    >
                        BOOKECHOES
                    </motion.h1>
                    <div style={{ position: 'absolute', top: '20px', right: '20px' }}>
                        <Link to="/admin" style={{ color: 'white', textDecoration: 'none', fontSize: '14px' }}>Admin</Link>
                    </div>
                </div>

                {/* The White Semi-Circle Hill Overlay */}
                <div className="white-curve-overlay">
                    {/* Navigation Pills SITTING ON THE CURVE */}
                    <div className="nav-pills-container">
                        {['BOOKS', 'NEWS & FEATURES', 'AUTHOR', 'EVENTS', 'PODCAST'].map((item, index) => (
                            <motion.button
                                key={item}
                                className="pill"
                                whileHover={{
                                    scale: 1.1,
                                    backgroundColor: '#4A5D23', // Keeping dynamic animation in JS or could move to CSS :hover but Framer Motion handles it well
                                }}
                                whileTap={{ scale: 0.95 }}
                                initial={{ opacity: 0, y: 50 }}
                                animate={{ opacity: 1, y: 0 }}
                                transition={{ delay: 0.5 + index * 0.1, type: "spring", stiffness: 120 }}
                            >
                                {item}
                            </motion.button>
                        ))}
                    </div>
                </div>
            </section>

            {/* 2. FIND YOUR FAVORITE BOOK */}
            <section className="section">
                <motion.h2
                    className="heading"
                    initial={{ opacity: 0 }}
                    whileInView={{ opacity: 1 }}
                >
                    FIND YOUR FAVORITE BOOK
                </motion.h2>

                <motion.div
                    className="dropdown"
                    whileHover={{ scale: 1.05, boxShadow: "0 10px 20px rgba(0,0,0,0.1)" }}
                >
                    <span className="dropdown-text">GENRES</span>
                    <FaArrowDown />
                </motion.div>
            </section>

            {/* 3. SPLIT SECTION */}
            <section className="split-section">
                <div className="split-col">
                    <h3 className="sub-heading">LATEST RELEASES</h3>
                </div>
                <div className="divider"></div>
                <div className="split-col">
                    <h3 className="sub-heading">COMING SOON</h3>
                </div>
            </section>

            {/* 4. BOOK MARKS FEATURES */}
            <section className="section">
                <h3 className="sub-heading text-left max-w-1000 mx-auto mb-2rem">
                    BOOK MARKS FEATURES
                </h3>
                <div className="grid">
                    {[1, 2, 3, 4].map((i) => (
                        <motion.div
                            key={i}
                            className="card"
                            whileHover={{ y: -10, boxShadow: "0 15px 30px rgba(0,0,0,0.2)" }}
                            transition={{ type: "spring", stiffness: 300 }}
                        >
                            <div className="card-internal"></div>
                        </motion.div>
                    ))}
                </div>
            </section>

            {/* 5. MORE FICTION CAROUSEL */}
            <section className="section">
                <h3 className="sub-heading">MORE FICTION</h3>
                <div className="carousel-container">
                    <div className="carousel-grid">
                        <AnimatePresence mode='wait'>
                            {getVisibleItems(fictionIndex).map((item) => (
                                <motion.div
                                    key={`fiction-${item}`}
                                    layout
                                    initial={{ opacity: 0, x: 20 }}
                                    animate={{ opacity: 1, x: 0 }}
                                    exit={{ opacity: 0, x: -20 }}
                                    className="small-card"
                                    whileHover={{ scale: 1.1 }}
                                >
                                    <div className="card-internal"></div>
                                </motion.div>
                            ))}
                        </AnimatePresence>
                    </div>

                    <motion.div
                        whileHover={{ scale: 1.2, x: 5 }}
                        whileTap={{ scale: 0.9 }}
                        className="arrow-container"
                        onClick={() => handleNext(fictionIndex, setFictionIndex)}
                    >
                        <FaArrowRight size={30} color="var(--color-dark-text)" />
                    </motion.div>
                </div>
            </section>

            {/* 6. NON FICTION CAROUSEL */}
            <section className="section" style={{ paddingBottom: '4rem' }}>
                <h3 className="sub-heading">NON FICTION</h3>
                <div className="carousel-container">
                    <div className="carousel-grid">
                        <AnimatePresence mode='wait'>
                            {getVisibleItems(nonFictionIndex).map((item) => (
                                <motion.div
                                    key={`nonfiction-${item}`}
                                    layout
                                    initial={{ opacity: 0, x: 20 }}
                                    animate={{ opacity: 1, x: 0 }}
                                    exit={{ opacity: 0, x: -20 }}
                                    className="small-card"
                                    whileHover={{ scale: 1.1 }}
                                >
                                    <div className="card-internal"></div>
                                </motion.div>
                            ))}
                        </AnimatePresence>
                    </div>
                    <motion.div
                        whileHover={{ scale: 1.2, x: 5 }}
                        whileTap={{ scale: 0.9 }}
                        className="arrow-container"
                        onClick={() => handleNext(nonFictionIndex, setNonFictionIndex)}
                    >
                        <FaArrowRight size={30} color="var(--color-dark-text)" />
                    </motion.div>
                </div>
            </section>

        </div>
    );
};

export default Home;
