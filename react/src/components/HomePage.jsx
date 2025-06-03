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
  const [categories, setCategories] = useState([]);
  const [isAdding, setIsAdding] = useState(false);
  const [addModalOpen, setAddModalOpen] = useState(false);
  const [editModalOpen, setEditModalOpen] = useState(false);
  const [selectedEquipment, setSelectedEquipment] = useState(null);
  const [isLoading, setIsLoading] = useState(true);
  const navigate = useNavigate();



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

  useEffect(() => {
    const fetchCategories = async () => {
      try {
        const response = await fetch('/api/category');
        const data = await response.json();
        setCategories(data);
      } catch (error) {
        console.error('Błąd pobierania kategorii:', error);
      }
    };
    fetchCategories();
  }, []);



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
      <Box className="home-content" sx={{ display: 'flex', minHeight: '100vh' }}>
        <NavMenu />
        <Box className="main-content" sx={{ flex: 1, padding: 3 }}>
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

          </Box>
            {isLoading ? (
            <Box sx={{ display: 'flex', justifyContent: 'center', my: 5 }}>
              <CircularProgress />
            </Box>
            ) : (
              <Grid container spacing={3} className="equipment-grid">
                {equipments.map((equipment) => (
                  <Grid item xs={12} sm={6} md={4} key={equipment.id}>
                    <EquipmentCard
                      equipment={equipment}
                      onClick={() => {
                        setSelectedEquipment(equipment);
                        setEditModalOpen(true);
                      }}
                    />
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
            categories={categories}

          />
          <EquipmentModal
            open={editModalOpen}
            onClose={() => setEditModalOpen(false)}
            equipment={selectedEquipment}
            categories={categories}
          />
        </Box>
      </Box>
    </div>
    
  );
};

export default HomePage;
