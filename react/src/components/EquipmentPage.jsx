import React, { useEffect, useState } from 'react';
import {
  Box,
  Grid,
  Card,
  CardContent,
  Typography,
  CircularProgress,
  TextField,
  MenuItem
} from '@mui/material';
import NavMenu from './NavMenu';
import Header from './Header';

const EquipmentPage = () => {
  const [equipments, setEquipments] = useState([]);
  const [filteredEquipments, setFilteredEquipments] = useState([]);
  const [isLoading, setIsLoading] = useState(true);
  const [search, setSearch] = useState('');
  const [categories, setCategories] = useState([]);
  const [selectedCategory, setSelectedCategory] = useState('');

  useEffect(() => {
    const fetchEquipments = async () => {
      setIsLoading(true);
      try {
        const response = await fetch('/api/equipment', {
          credentials: 'include',
        });
        const data = await response.json();
        setEquipments(data);
        setFilteredEquipments(data);
      } catch (error) {
        console.error('Błąd podczas pobierania sprzętu:', error);
      } finally {
        setIsLoading(false);
      }
    };

    fetchEquipments();
  }, []);

  useEffect(() => {
    const fetchCategories = async () => {
      try {
        const response = await fetch('/api/category', {
          credentials: 'include',
        });
        const data = await response.json();
        setCategories(data);
      } catch (error) {
        console.error('Błąd podczas pobierania kategorii:', error);
      }
    };

    fetchCategories();
  }, []);

  useEffect(() => {
    let filtered = equipments;

    if (search) {
      filtered = filtered.filter(e =>
        e.name.toLowerCase().includes(search.toLowerCase())
      );
    }

    if (selectedCategory) {
      filtered = filtered.filter(e => e.categoryid === selectedCategory);
    }

    setFilteredEquipments(filtered);
  }, [search, selectedCategory, equipments]);

  return (
    <div className="home-container">
      <Header />
      <Box className="home-content" sx={{ display: 'flex', overflow: 'hidden', height: '100vh' }}>
        <NavMenu />
        <Box className="main-content" sx={{ flex: 1, padding: 3, maxHeight: '100vh', overflowY: 'auto' }}>
          <Box sx={{ display: 'flex', gap: 2, mb: 3 }}>
            <TextField
              label="Szukaj po nazwie"
              variant="outlined"
              fullWidth
              value={search}
              onChange={e => setSearch(e.target.value)}
            />
            <TextField
              label="Kategoria"
              select
              fullWidth
              value={selectedCategory}
              onChange={e => setSelectedCategory(e.target.value)}
            >
              <MenuItem value="">Wszystkie</MenuItem>
              {categories.map(cat => (
                <MenuItem key={cat.id} value={cat.id}>
                  {cat.nazwa}
                </MenuItem>
              ))}
            </TextField>
          </Box>

          {isLoading ? (
            <Box sx={{ display: 'flex', justifyContent: 'center', mt: 5 }}>
              <CircularProgress />
            </Box>
          ) : (
            <Grid container spacing={3}>
              {filteredEquipments.map((equipment) => (
                <Grid item xs={12} sm={6} md={4} key={equipment.id}>
                  <Card variant="outlined" sx={{ height: '100%' }}>
                    <CardContent>
                      <Typography variant="h6" gutterBottom>
                        {equipment.name}
                      </Typography>
                      <Typography variant="body2" color="textSecondary">
                        <strong>Opis:</strong> {equipment.description}
                      </Typography>
                      <Typography variant="body2">
                        <strong>Ilość:</strong> {equipment.quantity}
                      </Typography>
                      <Typography variant="body2">
                        <strong>Kategoria:</strong> {equipment.category}
                      </Typography>
                      <Typography variant="body2">
                        <strong>Cena:</strong> {equipment.price} zł
                      </Typography>
                      <Typography variant="body2">
                        <strong>Dodatkowe informacje:</strong> {equipment.additional_info}
                      </Typography>
                      <Typography variant="body2">
                        <strong>Informacje do wyceny:</strong> {equipment.pricing_info}
                      </Typography>
                    </CardContent>
                  </Card>
                </Grid>
              ))}
            </Grid>
          )}
        </Box>
      </Box>
    </div>
  );
};

export default EquipmentPage;
