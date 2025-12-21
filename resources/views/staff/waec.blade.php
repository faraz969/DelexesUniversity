@extends('layouts.app')

@section('content')
<div class="container py-3">
  <h2>WAEC Results Lookup</h2>
  <div class="card" style="border-radius:10px;">
    <div class="card-body">
      <form id="waecLookupForm" onsubmit="return false;">
        @csrf
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">Exam Type</label>
            <select id="examtype" class="form-select" required>
              <option value="">-- Select --</option>
              <option value="1">WASSCE School</option>
              <option value="2">WASSCE Private</option>
              <option value="3">BECE School</option>
              <option value="4">BECE Private</option>
              <option value="5">SSCE School</option>
              <option value="6">SSCE Private</option>
              <option value="7">School ABCE</option>
              <option value="8">School GBCE</option>
              <option value="9">Private ABCE</option>
              <option value="10">Private GBCE</option>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Exam Year</label>
            <input id="examyear" type="number" class="form-control" placeholder="2023" required />
          </div>
          <div class="col-md-5">
            <label class="form-label">Index Number</label>
            <div class="d-flex gap-2">
              <input id="cindex" type="text" class="form-control" placeholder="0010408006" required />
              <button id="fetchBtn" type="button" class="btn btn-primary">Fetch</button>
            </div>
          </div>
        </div>
      </form>

      <div id="candidateBox" class="mt-3" style="display:none;">
        <h5 class="mb-2">Candidate</h5>
        <div id="candMeta" class="small text-muted"></div>
      </div>

      <div class="mt-3" id="resultsBox" style="display:none;">
        <div class="d-flex justify-content-between align-items-center">
          <h5 class="mb-2">Results</h5>
          <form id="exportForm" method="get" action="{{ route('waec.export') }}">
            <input type="hidden" name="examtype" id="export_examtype" />
            <input type="hidden" name="examyear" id="export_examyear" />
            <input type="hidden" name="cindex" id="export_cindex" />
            <button type="submit" class="btn btn-outline-secondary">Export CSV</button>
          </form>
        </div>
        <div class="table-responsive">
          <table class="table table-striped table-bordered" id="resultsTable">
            <thead>
              <tr>
                <th>Subject Code</th>
                <th>Subject</th>
                <th>Grade (Letter)</th>
                <th>Grade (Number)</th>
                <th>Interpretation</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  (function(){
    function mapWaecLetterToNumber(letter) {
      if (!letter) return '';
      const t = String(letter).trim().toUpperCase();
      const map = { 'A1':1, 'B2':2, 'B3':3, 'C4':4, 'C5':5, 'C6':6, 'D7':7, 'E8':8, 'F9':9 };
      return map[t] ?? '';
    }

    const btn = document.getElementById('fetchBtn');
    btn.addEventListener('click', async () => {
      const examtype = document.getElementById('examtype').value.trim();
      const examyear = document.getElementById('examyear').value.trim();
      const cindex = document.getElementById('cindex').value.trim();
      if (!examtype || !examyear || !cindex) {
        alert('Please complete Exam Type, Exam Year and Index Number.');
        return;
      }
      btn.disabled = true; btn.textContent = 'Fetching...';
      try {
        const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const res = await fetch('{{ route('waec.fetch') }}', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
          body: JSON.stringify({ cindex, examyear, examtype: Number(examtype) })
        });
        const data = await res.json();
        if (!res.ok) throw new Error('Fetch failed');
        const ok = data && data.reqstatus && Number(data.reqstatus.msgcode) === 0 && Array.isArray(data.resultdetails);
        if (!ok) { alert('No results or verification failed.'); return; }

        // Candidate info
        const cand = data.candidate || {};
        const candBox = document.getElementById('candidateBox');
        const candMeta = document.getElementById('candMeta');
        candMeta.textContent = `${cand.cname || ''} | Index: ${cand.cindex || cindex} | DOB: ${cand.dob || ''}`;
        candBox.style.display = 'block';

        // Results table
        const tbody = document.querySelector('#resultsTable tbody');
        tbody.innerHTML = '';
        (data.resultdetails || []).forEach(r => {
          const tr = document.createElement('tr');
          tr.innerHTML = `
            <td>${r.subjectcode || ''}</td>
            <td>${r.subject || ''}</td>
            <td>${r.grade || ''}</td>
            <td>${mapWaecLetterToNumber(r.grade)}</td>
            <td>${r.interpretation || r.intrepretation || ''}</td>
          `;
          tbody.appendChild(tr);
        });
        document.getElementById('resultsBox').style.display = 'block';

        // Set export params
        document.getElementById('export_examtype').value = examtype;
        document.getElementById('export_examyear').value = examyear;
        document.getElementById('export_cindex').value = cindex;
      } catch (e) {
        alert('Error fetching results.');
      } finally {
        btn.disabled = false; btn.textContent = 'Fetch';
      }
    });
  })();
</script>
@endsection


