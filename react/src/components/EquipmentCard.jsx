import React from 'react';
import { Card, CardContent, Typography } from '@mui/material';

const EquipmentCard = ({ equipment, onClick }) => (
  <Card 
    sx={{ width: 'auto', margin: '10px', backgroundColor: '#e0e0e0', cursor: 'pointer' }}
    onClick={onClick}
  >
    <CardContent>
      <Typography variant="h6">{equipment.name}</Typography>
      <Typography variant="body2">Ilość: {equipment.quantity}</Typography>
      <Typography variant="body2">Cena: {equipment.price} PLN</Typography>
    </CardContent>
  </Card>
);

export default EquipmentCard;