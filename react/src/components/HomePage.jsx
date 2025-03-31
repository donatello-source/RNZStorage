// src/components/HomePage.js
import React, { useEffect, useState } from 'react';
import { Box, Grid, Typography } from '@mui/material';
import EquipmentCard from './EquipmentCard';
import NavMenu from './NavMenu';
import Header from './Header';

const HomePage = () => {
  const [equipments, setEquipments] = useState([]);

  useEffect(() => {
    const fetchData = async () => {
      const response = await fetch('/api/equipment');
      const data = await response.json();
      setEquipments(data);
    };

    fetchData();
  }, []);

  return (
    <Box sx={{ display: 'flex', backgroundColor: '#333', height: '100vh' }}>
      <NavMenu />
      <Box sx={{ flexGrow: 1, padding: '20px', backgroundColor: '#4e4e4e' }}>
        <Header />
        <Typography variant="h4" sx={{ color: '#fff', marginBottom: '20px' }}>
          Witaj na stronie głównej
        </Typography>
        <Grid container spacing={2}>
          {equipments.map((equipment) => (
            <Grid item xs={12} sm={6} md={4} key={equipment.id}>
              <EquipmentCard equipment={equipment} />
            </Grid>
          ))}
        </Grid>
      </Box>
    </Box>
  );
};

export default HomePage;
