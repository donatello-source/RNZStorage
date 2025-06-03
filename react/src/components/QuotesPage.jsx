import React, { useEffect, useState } from 'react';
import { Box, Typography, Grid, CircularProgress, Alert, TextField, Dialog, DialogTitle, DialogContent, DialogActions, Button } from '@mui/material';
import QuoteCard from './QuoteCard';
import Header from './Header';
import NavMenu from './NavMenu';

const getQuoteBgColor = (quote) => {
  const status = (quote.status || '').toLowerCase();
  const dateStr = quote.dataWystawienia;
  if (!dateStr) return 'white';

  const date = new Date(dateStr);
  const now = new Date();
  const diffDays = Math.floor((now - date) / (1000 * 60 * 60 * 24));

  if (status === 'przyjęta') return '#c8e6c9';
  if (status === 'odrzucona' || diffDays > 7) return '#ffcdd2';
  if (diffDays <= 7) return '#ffe0b2';
  return 'white';
};

const QuotesPage = () => {
  const [quotes, setQuotes] = useState([]);
  const [filteredQuotes, setFilteredQuotes] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [search, setSearch] = useState('');
  const [selectedQuote, setSelectedQuote] = useState(null);
  const [showModal, setShowModal] = useState(false);
  const [statusLoading, setStatusLoading] = useState(false);

  useEffect(() => {
    const fetchQuotes = async () => {
      try {
        const res = await fetch('/api/quotation');
        if (!res.ok) throw new Error('Błąd pobierania wycen');
        const data = await res.json();
        setQuotes(data);
        setFilteredQuotes(data);
      } catch (e) {
        setError(e.message);
      } finally {
        setLoading(false);
      }
    };
    fetchQuotes();
  }, []);

  useEffect(() => {
    if (!search) {
      setFilteredQuotes(quotes);
    } else {
      setFilteredQuotes(
        quotes.filter(q =>
          (q.company?.name || '').toLowerCase().includes(search.toLowerCase()) ||
          (q.lokalizacja || '').toLowerCase().includes(search.toLowerCase()) ||
          (q.status || '').toLowerCase().includes(search.toLowerCase()) ||
          (q.info || '').toLowerCase().includes(search.toLowerCase())
        )
      );
    }
  }, [search, quotes]);

  const handleStatusChange = async (status) => {
    if (!selectedQuote) return;
    setStatusLoading(true);
    try {
      const res = await fetch(`/api/quotation/${selectedQuote.id}/status`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ status }),
      });
      if (!res.ok) throw new Error('Błąd zmiany statusu');
      const updated = quotes.map(q =>
        q.id === selectedQuote.id ? { ...q, status } : q
      );
      setQuotes(updated);
      setFilteredQuotes(updated);
      setShowModal(false);
    } catch (e) {
      alert(e.message);
    } finally {
      setStatusLoading(false);
    }
  };

  return (
    <div className="home-container">
      <Header />
      <Box className="home-content" sx={{ display: 'flex', overflow: 'hidden', height: '100vh' }}>
        <NavMenu />
        <Box className="main-content" sx={{ flex: 1, padding: 3, height: '100vh', overflowY: 'auto' }}>
          <Typography variant="h4" gutterBottom>
            Wyceny
          </Typography>
          <Box sx={{ mb: 3 }}>
            <TextField
              label="Szukaj"
              variant="outlined"
              fullWidth
              value={search}
              onChange={e => setSearch(e.target.value)}
            />
          </Box>
          {loading ? (
            <Box sx={{ display: 'flex', justifyContent: 'center', mt: 5 }}>
              <CircularProgress />
            </Box>
          ) : error ? (
            <Alert severity="error">{error}</Alert>
          ) : (
            <Grid container spacing={2}>
              {filteredQuotes.length === 0 ? (
                <Typography>Brak wycen.</Typography>
              ) : (
                filteredQuotes.map(quote => (
                  <Grid item xs={12} sm={6} md={4} key={quote.id}>
                    <QuoteCard
                      quote={quote}
                      bgColor={getQuoteBgColor(quote)}
                      onClick={() => {
                        setSelectedQuote(quote);
                        setShowModal(true);
                      }}
                    />
                  </Grid>
                ))
              )}
            </Grid>
          )}
          <Dialog open={showModal} onClose={() => setShowModal(false)}>
            <DialogTitle>
              Zmień status wyceny
            </DialogTitle>
            <DialogContent>
              <Typography>
                Firma: {selectedQuote?.company?.name || selectedQuote?.company}
              </Typography>
              <Typography>
                Status: {selectedQuote?.status}
              </Typography>
              <Typography>
                Data wystawienia: {selectedQuote?.dataWystawienia}
              </Typography>
              <Typography>
                Miejsce: {selectedQuote?.lokalizacja}
              </Typography>
              <Typography>
                Netto: {selectedQuote?.netto} zł
              </Typography>
              <Typography>
                Brutto: {selectedQuote?.brutto} zł
              </Typography>
            </DialogContent>
            <DialogActions>
              <Button
                color="success"
                disabled={statusLoading}
                onClick={() => handleStatusChange('przyjęta')}
              >
                Ustaw jako przyjęta
              </Button>
              <Button
                color="error"
                disabled={statusLoading}
                onClick={() => handleStatusChange('odrzucona')}
              >
                Ustaw jako odrzucona
              </Button>
              <Button
                variant="outlined"
                onClick={() => {
                  setShowModal(false);
                  window.location.href = `/quote/${selectedQuote.id}/edit`;
                }}
              >
                Podgląd
              </Button>
              <Button onClick={() => setShowModal(false)}>Zamknij</Button>
            </DialogActions>
          </Dialog>
        </Box>
      </Box>
    </div>
  );
};

export default QuotesPage;