// src/components/EquipmentCard.js
import React from 'react';
import { Card, CardContent, Typography } from '@mui/material';

const EquipmentCard = ({ equipment }) => {
  return (
    <Card sx={{ width: '30%', margin: '10px', backgroundColor: '#e0e0e0' }}>
      <CardContent>
        <Typography variant="h6">{equipment.nazwa}</Typography>
        <Typography variant="body2">Liczba: {equipment.ilosc}</Typography>
        <Typography variant="body2">Cena: {equipment.cena} PLN</Typography>
      </CardContent>
    </Card>
  );
};

export default EquipmentCard;
