import { Card, CardContent, Typography, Box, Divider } from '@mui/material';

const QuoteCard = ({ quote, bgColor, onClick }) => (
  <Card sx={{ mb: 2, background: bgColor || 'white', cursor: 'pointer' }} onClick={onClick}>
    <CardContent>
      <Typography variant="h6" gutterBottom>
        {quote.company?.name || quote.company || 'Brak nazwy firmy'}
      </Typography>
            <Typography variant="body2" gutterBottom>
        Numer: {quote.id || 'Brak'}
      </Typography>
      <Typography variant="body2" gutterBottom>
        Status: {quote.status || 'Brak'}
      </Typography>
      <Typography variant="body2" gutterBottom>
        Data wystawienia: {quote.dataWystawienia || 'Brak'}
      </Typography>
      <Typography variant="body2" gutterBottom>
        Miejsce: {quote.lokalizacja || 'Brak'}
      </Typography>
      <Divider sx={{ my: 1 }} />
      <Box sx={{ display: 'flex', justifyContent: 'space-between' }}>
        <Typography variant="body1" fontWeight="bold">
          Netto: {quote.netto ? `${quote.netto} zł` : 'Brak'}
        </Typography>
        <Typography variant="body1" fontWeight="bold">
          Brutto: {quote.brutto ? `${quote.brutto} zł` : 'Brak'}
        </Typography>
      </Box>
    </CardContent>
  </Card>
);

export default QuoteCard;