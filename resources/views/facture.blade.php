<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<title>Facture {{ $facture->numero_facture }}</title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }

body {
  font-family: 'DejaVu Sans', Arial, sans-serif;
  font-size: 12px;
  color: #1e293b;
  background: #fff;
}

/* ── PAGE ── */
.page {
  padding: 36px 40px;
  min-height: 100vh;
}

/* ── HEADER ── */
.header {
  display: table;
  width: 100%;
  margin-bottom: 36px;
  border-bottom: 2px solid #6366f1;
  padding-bottom: 24px;
}

.header-left  { display: table-cell; vertical-align: middle; width: 50%; }
.header-right { display: table-cell; vertical-align: middle; width: 50%; text-align: right; }

/* Logo / marque */
.brand {
  font-size: 26px;
  font-weight: 900;
  color: #6366f1;
  letter-spacing: 1px;
}

.brand-sub {
  font-size: 10px;
  color: #94a3b8;
  letter-spacing: 2px;
  text-transform: uppercase;
  margin-top: 2px;
}

/* Badge FACTURE */
.invoice-badge {
  display: inline-block;
  background: #6366f1;
  color: #fff;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 2px;
  text-transform: uppercase;
  padding: 5px 14px;
  border-radius: 20px;
  margin-bottom: 6px;
}

.invoice-number {
  font-size: 22px;
  font-weight: 800;
  color: #0f172a;
}

.invoice-date {
  font-size: 11px;
  color: #64748b;
  margin-top: 4px;
}

/* ── INFO ROW ── */
.info-row {
  display: table;
  width: 100%;
  margin-bottom: 32px;
}

.info-block {
  display: table-cell;
  width: 48%;
  vertical-align: top;
  padding: 16px 20px;
  border-radius: 10px;
}

.info-block.left  {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  margin-right: 4%;
}

.info-block.right {
  background: #f0f4ff;
  border: 1px solid #c7d2fe;
}

.info-block-title {
  font-size: 9px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 1.5px;
  color: #94a3b8;
  margin-bottom: 10px;
}

.info-name {
  font-size: 14px;
  font-weight: 700;
  color: #0f172a;
  margin-bottom: 4px;
}

.info-line {
  font-size: 11px;
  color: #64748b;
  margin-bottom: 2px;
}

/* ── STATUS BADGE ── */
.status-badge {
  display: inline-block;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 10px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-top: 8px;
}

.status-payee     { background: #dcfce7; color: #16a34a; }
.status-en_attente { background: #fef9c3; color: #ca8a04; }
.status-brouillon { background: #f1f5f9; color: #64748b; }
.status-annulee   { background: #fee2e2; color: #dc2626; }

/* ── TABLE ── */
.table-wrap {
  margin-bottom: 24px;
}

.table-title {
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 1px;
  color: #6366f1;
  margin-bottom: 10px;
}

table {
  width: 100%;
  border-collapse: collapse;
}

thead tr {
  background: #6366f1;
}

thead th {
  color: #fff;
  font-size: 10px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  padding: 10px 14px;
  text-align: left;
}

thead th:last-child { text-align: right; }

tbody tr {
  border-bottom: 1px solid #f1f5f9;
}

tbody tr:nth-child(even) {
  background: #f8fafc;
}

tbody td {
  padding: 10px 14px;
  font-size: 11px;
  color: #334155;
  vertical-align: middle;
}

tbody td:last-child {
  text-align: right;
  font-weight: 700;
  color: #0f172a;
}

.td-designation { font-weight: 600; color: #0f172a; }
.td-center      { text-align: center; }

/* ── TOTAUX ── */
.totaux-wrap {
  display: table;
  width: 100%;
  margin-top: 8px;
}

.totaux-spacer { display: table-cell; width: 55%; }

.totaux-box {
  display: table-cell;
  width: 45%;
  vertical-align: top;
}

.total-row {
  display: table;
  width: 100%;
  padding: 7px 14px;
  border-bottom: 1px solid #f1f5f9;
}

.total-label {
  display: table-cell;
  font-size: 11px;
  color: #64748b;
}

.total-val {
  display: table-cell;
  text-align: right;
  font-size: 11px;
  font-weight: 600;
  color: #334155;
}

.total-ttc {
  background: #6366f1;
  border-radius: 8px;
  margin-top: 6px;
  padding: 12px 14px;
}

.total-ttc .total-label {
  font-size: 12px;
  font-weight: 700;
  color: #fff;
}

.total-ttc .total-val {
  font-size: 16px;
  font-weight: 900;
  color: #fff;
}

/* ── QR + FOOTER ── */
.bottom-row {
  display: table;
  width: 100%;
  margin-top: 36px;
  padding-top: 20px;
  border-top: 1px solid #e2e8f0;
}

.qr-block {
  display: table-cell;
  width: 120px;
  vertical-align: middle;
  text-align: center;
}

.qr-block img {
  width: 90px;
  height: 90px;
  border: 3px solid #e2e8f0;
  border-radius: 10px;
  padding: 4px;
}

.qr-label {
  font-size: 9px;
  color: #94a3b8;
  margin-top: 6px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.footer-block {
  display: table-cell;
  vertical-align: middle;
  padding-left: 24px;
}

.footer-brand {
  font-size: 14px;
  font-weight: 800;
  color: #6366f1;
  margin-bottom: 4px;
}

.footer-text {
  font-size: 10px;
  color: #94a3b8;
  line-height: 1.6;
}

/* DGI STATUS */
.dgi-block {
  display: table-cell;
  vertical-align: middle;
  text-align: right;
}

.dgi-badge {
  display: inline-block;
  padding: 6px 14px;
  border-radius: 8px;
  font-size: 10px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.dgi-pending   { background: #fef9c3; color: #ca8a04; border: 1px solid #fde68a; }
.dgi-validated { background: #dcfce7; color: #16a34a; border: 1px solid #bbf7d0; }
.dgi-rejected  { background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; }

.dgi-label {
  font-size: 9px;
  color: #94a3b8;
  text-transform: uppercase;
  letter-spacing: 1px;
  margin-bottom: 4px;
}
</style>
</head>

<body>
<div class="page">

  <!-- ── HEADER ── -->
  <div class="header">
    <div class="header-left">
      <div class="brand">GESTOVIA</div>
      <div class="brand-sub">Business Finance Platform</div>
    </div>
    <div class="header-right">
      <div class="invoice-badge">Facture</div>
      <div class="invoice-number">{{ $facture->numero_facture }}</div>
      <div class="invoice-date">
        Émise le {{ \Carbon\Carbon::parse($facture->date_facture)->format('d/m/Y') }}
        @if($facture->date_echeance)
          · Échéance le {{ \Carbon\Carbon::parse($facture->date_echeance)->format('d/m/Y') }}
        @endif
      </div>
    </div>
  </div>

  <!-- ── INFOS ── -->
  <div class="info-row">
    <div class="info-block left">
      <div class="info-block-title">Émetteur</div>
      <div class="info-name">Gestovia</div>
      <div class="info-line">Plateforme de gestion d'entreprise</div>
      <div class="info-line">support@gestovia.com</div>
    </div>

    <div class="info-block right">
      <div class="info-block-title">Facturé à</div>
      <div class="info-name">{{ $facture->client->nom ?? '—' }}</div>
      @if($facture->client->entreprise)
        <div class="info-line">{{ $facture->client->entreprise }}</div>
      @endif
      @if($facture->client->email)
        <div class="info-line">{{ $facture->client->email }}</div>
      @endif
      @if($facture->client->telephone)
        <div class="info-line">{{ $facture->client->telephone }}</div>
      @endif
      @if($facture->client->adresse)
        <div class="info-line">{{ $facture->client->adresse }}</div>
      @endif
      <span class="status-badge status-{{ $facture->statut }}">
        @switch($facture->statut)
          @case('payee') Payée @break
          @case('en_attente') En attente @break
          @case('brouillon') Brouillon @break
          @case('annulee') Annulée @break
          @default {{ $facture->statut }}
        @endswitch
      </span>
    </div>
  </div>

  <!-- ── TABLE LIGNES ── -->
  <div class="table-wrap">
    <div class="table-title">Détail des produits / services</div>
    <table>
      <thead>
        <tr>
          <th style="width:45%">Désignation</th>
          <th style="width:15%; text-align:center">Quantité</th>
          <th style="width:20%; text-align:right">Prix unitaire</th>
          <th style="width:20%">Sous-total</th>
        </tr>
      </thead>
      <tbody>
        @foreach($facture->lignes as $ligne)
        <tr>
          <td class="td-designation">{{ $ligne->designation }}</td>
          <td class="td-center">{{ $ligne->quantite }}</td>
          <td style="text-align:right">{{ number_format($ligne->prix_unitaire, 2, ',', ' ') }} DT</td>
          <td>{{ number_format($ligne->sous_total, 2, ',', ' ') }} DT</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  <!-- ── TOTAUX ── -->
  <div class="totaux-wrap">
    <div class="totaux-spacer"></div>
    <div class="totaux-box">
      <div class="total-row">
        <span class="total-label">Sous-total HT</span>
        <span class="total-val">{{ number_format($facture->total_ht, 2, ',', ' ') }} DT</span>
      </div>
      <div class="total-row">
        <span class="total-label">TVA (19%)</span>
        <span class="total-val">{{ number_format($facture->tva, 2, ',', ' ') }} DT</span>
      </div>
      <div class="total-row total-ttc">
        <span class="total-label">Total TTC</span>
        <span class="total-val">{{ number_format($facture->total_ttc, 2, ',', ' ') }} DT</span>
      </div>
    </div>
  </div>

  <!-- ── BOTTOM ── -->
  <div class="bottom-row">

    <!-- QR CODE -->
    <div class="qr-block">
      <img src="data:image/png;base64,{{ $qrCode }}" alt="QR Code">
      <div class="qr-label">Vérifier</div>
    </div>

    <!-- FOOTER -->
    <div class="footer-block">
      <div class="footer-brand">GESTOVIA</div>
      <div class="footer-text">
        Plateforme intelligente de gestion d'entreprise<br>
        Ce document est généré automatiquement et fait foi.<br>
        Pour toute question : support@gestovia.com
      </div>
    </div>

    <!-- DGI STATUS -->
    <div class="dgi-block">
      <div class="dgi-label">Statut DGI</div>
      @php $dgi = $facture->statut_dgi ?? 'pending'; @endphp
      <span class="dgi-badge dgi-{{ $dgi }}">
        @switch($dgi)
          @case('validated') ✓ Validée @break
          @case('rejected')  ✗ Rejetée @break
          @default En attente
        @endswitch
      </span>
      @if($facture->commentaire_dgi)
        <div class="footer-text" style="margin-top:6px; max-width:180px; text-align:right">
          {{ $facture->commentaire_dgi }}
        </div>
      @endif
    </div>

  </div>

</div>
</body>
</html>