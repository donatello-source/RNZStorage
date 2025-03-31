import React, { useEffect, useState } from 'react';
import { Box, Grid, Typography, Button } from '@mui/material';
import EquipmentCard from './EquipmentCard';
import NavMenu from './NavMenu';
import Header from './Header';
import { useNavigate } from 'react-router-dom';
import '../style/HomePage.css';

const HomePage = () => {
  const [equipments, setEquipments] = useState([]);
  const [user, setUser] = useState(null);
  const navigate = useNavigate();

  useEffect(() => {
    const fetchData = async () => {
      const response = await fetch('/api/equipment');
      const data = await response.json();
      console.log(data);
      setEquipments(data);
    };

    fetchData();

    const storedUser = JSON.parse(localStorage.getItem('user'));
    const token = localStorage.getItem('token');

    if (!storedUser || !token) {
      navigate('/');
    } else {
      setUser(storedUser);
    }
  }, [navigate]);

  const handleLogout = () => {
    localStorage.removeItem('user');
    localStorage.removeItem('token');
    navigate('/');
  };

  return (
    <div className="home-container">
      <Header />
      <Box className="home-content">
        <NavMenu />
        <Box className="main-content">
          <Box className="welcome-header">
            <Typography variant="h4" className="welcome-text">
              Witaj {user ? `${user.imie} ${user.nazwisko}` : 'Gościu'}!
            </Typography>
            <Button className="logout-button" variant="contained" onClick={handleLogout}>
              Wyloguj się
            </Button>
          </Box>
          <Grid container spacing={3} className="equipment-grid">
            {equipments.map((equipment) => (
              <Grid item xs={12} sm={6} md={4} key={equipment.id}>
                <EquipmentCard equipment={equipment} />
              </Grid>
            ))}
          </Grid>
        </Box>
      </Box>
    </div>
  );
};

export default HomePage;
