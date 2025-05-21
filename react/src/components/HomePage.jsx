import React, { useEffect, useState } from 'react';
import { Box, Grid, Typography, Button } from '@mui/material';
import EquipmentCard from './EquipmentCard';
import NavMenu from './NavMenu';
import Header from './Header';
import EquipmentModal from './EquipmentModal';
import { useNavigate } from 'react-router-dom';
import CircularProgress from '@mui/material/CircularProgress';
import '../style/HomePage.css';

const HomePage = () => {
  const [equipments, setEquipments] = useState([]);
  const [user, setUser] = useState(null);
  const navigate = useNavigate();
  const [isAdding, setIsAdding] = useState(false);
  const [addModalOpen, setAddModalOpen] = useState(false);
  const [isLoading, setIsLoading] = useState(true);

    
 useEffect(() => {
    const storedUser = JSON.parse(localStorage.getItem('user'));
  
    if (!storedUser) {
      navigate('/');
    } else if (storedUser.role.includes('ROLE_ADMIN')) {
      navigate('/admin');
    }
  }, [navigate]);
  
  useEffect(() => {
    const fetchData = async () => {
      setIsLoading(true);
      try {
        const response = await fetch('/api/equipment', {
          credentials: 'include'
        });
        const data = await response.json();
        setEquipments(data);
      } catch (error) {
        console.error('Błąd podczas pobierania danych:', error);
      } finally {
        setIsLoading(false);
      }
    };

    fetchData();

    const storedUser = JSON.parse(localStorage.getItem('user'));

    if (!storedUser) {
      navigate('/');
    } else {
      setUser(storedUser);
    }
  }, [navigate]);


  const handleLogout = async () => {
    localStorage.removeItem('user');
    document.cookie = 'PHPSESSID=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
    navigate('/');
  };

  const refreshEquipment = async () => {
  setIsLoading(true);
  try {
    const response = await fetch('/api/equipment', {
      credentials: 'include'
    });
    const data = await response.json();
    setEquipments(data);
  } catch (error) {
    console.error('Błąd podczas pobierania danych:', error);
  } finally {
    setIsLoading(false);
  }
  };

  const handleAddClick = () => {
    setIsAdding(true);
    setAddModalOpen(true);
  };

  return (
    <div className="home-container">
      <Header />
      <Box className="home-content">
        <NavMenu />
        <Box className="main-content">
          <Box className="welcome-header">
          <Box sx={{ display: 'flex', alignItems: 'center', gap: 2 }}>
              <Typography variant="h4" className="welcome-text">
                Witaj {user ? `${user.imie} ${user.nazwisko}` : 'Gościu'}!
              </Typography>
              <Button 
                variant="outlined" 
                size="small" 
                onClick={refreshEquipment}
              >
                Odśwież
              </Button>
            </Box>
            <Button className="logout-button" variant="contained" onClick={handleLogout}>
              Wyloguj się
            </Button>
          </Box>
            {isLoading ? (
            <Box sx={{ display: 'flex', justifyContent: 'center', my: 5 }}>
              <CircularProgress />
            </Box>
            ) : (
              <Grid container spacing={3} className="equipment-grid">
                {equipments.map((equipment) => (
                  <Grid item xs={12} sm={6} md={4} key={equipment.id}>
                    <EquipmentCard equipment={equipment} />
                  </Grid>
                ))}
              </Grid>
            )}
          <Button
            variant="contained"
            color="primary"
            sx={{
              position: 'fixed',
              bottom: 20,
              right: 20,
            }}
            onClick={handleAddClick}
          >
            Dodaj sprzęt
          </Button>
          <EquipmentModal
            open={addModalOpen}
            onClose={() => setAddModalOpen(false)}
            equipment={null}
            isAdding={true}
          />
        </Box>
      </Box>
    </div>
    
  );
};

export default HomePage;
