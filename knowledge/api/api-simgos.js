// ANTRIAN DISPLAY - JADWAL PRAKTEK DOKTER (SLIDE OTOMATIS)
window.SIMPEL_JADWAL_DOKTER_API_URL ="https://demo.syifaglobalgroup.com/api/banjarbaru/jadwal-harian";

// RIWAYAT ORDER RESEP - DETIL OVERRIDE
// - Perlebar kolom Nama Obat
// - Tambah kolom indikator ▲ untuk baris yang nilainya diubah farmasi
Ext.define("layanan.resep.riwayat.DetilOverride", {
  override: "layanan.resep.riwayat.Detil",
  initComponent: function () {
    var me = this;
    me.callParent(arguments);

    // Perlebar kolom Nama Obat
    me.getColumns().forEach(function (col) {
      if (col.text === "Nama Obat") {
        col.setFlex(3);
        col.setMinWidth(180);
      }
    });

    // Wrap renderer tiap kolom yang ingin dibandingkan
    // Jika nilainya beda dengan order dokter: tampilkan segitiga sudut kanan atas + tooltip
    var kolomBanding = {
      DOSIS: {
        getLabel: function (val) {
          return val || "-";
        },
      },
      JUMLAH: {
        getLabel: function (val) {
          return val || "-";
        },
      },
      FREKUENSI: {
        getLabel: function (val, rec) {
          return (
            ((rec.get("REFERENSI") || {}).FREKUENSI || {}).FREKUENSI ||
            val ||
            "-"
          );
        },
      },
      RUTE_PEMBERIAN: {
        getLabel: function (val, rec) {
          return (
            ((rec.get("REFERENSI") || {}).RUTE_PEMBERIAN || {}).DESKRIPSI ||
            val ||
            "-"
          );
        },
      },
      KETERANGAN: {
        getLabel: function (val) {
          return val || "-";
        },
      },
    };

    me.getColumns().forEach(function (col) {
      var cfg = kolomBanding[col.dataIndex];
      if (!cfg) return;

      var origRenderer = col.renderer;
      col.renderer = (function (orig, getLabel) {
        return function (value, meta, record) {
          // Jalankan renderer asli (string = controller method, function = langsung)
          var ctrl = me.getController();
          var hasil;
          if (typeof orig === "string" && ctrl && ctrl[orig]) {
            hasil = ctrl[orig](value, meta, record);
          } else if (typeof orig === "function") {
            hasil = orig.call(col, value, meta, record);
          } else {
            hasil = value !== undefined && value !== null ? String(value) : "";
          }

          // Bandingkan dengan order dokter
          var idOD = record.get("ID_ORDER_DETAIL");
          if (!idOD || idOD <= 0) return hasil;

          var workspace = me.up("riwayat-resep-workspace");
          if (!workspace) return hasil;
          var dokterStore = workspace.getViewModel().get("detilresepstore");
          var dokter = dokterStore ? dokterStore.getById(idOD) : null;
          if (!dokter) return hasil;

          var fVal = record.get(col.dataIndex);
          var dVal = dokter.get(col.dataIndex);
          if (String(fVal || "") === String(dVal || "")) return hasil;

          var dLabel = getLabel(dVal, dokter);
          var fLabel = getLabel(fVal, record);

          meta.tdCls = (meta.tdCls || "") + " cell-ubah-farmasi";
          meta.tdAttr =
            'data-qtip="' +
            Ext.String.htmlEncode(
              "Dokter: " + dLabel + " → Farmasi: " + fLabel
            ) +
            '" data-qwidth="200"';
          return hasil;
        };
      })(origRenderer, cfg.getLabel);
    });
  },
  loadStore: function (l) {
    l = l || {};
    l.GET_ORDER_SEBELUMNYA = 1;
    this.callParent([l]);
  },
});

// RIWAYAT ORDER RESEP - WORKSPACE CONTROLLER: load detilresepstore saat order dipilih
// supaya indikator ▲ di grid Order Dokter punya data untuk dibandingkan
Ext.define("layanan.resep.riwayat.WorkspaceControllerOverride", {
  override: "layanan.resep.riwayat.WorkspaceController",
  onOrderSelected: function (f, k) {
    this.callParent(arguments);
    var orderId = k.get("REF");
    if (!orderId) return;
    var dokterStore = this.getView().getViewModel().get("detilresepstore");
    if (!dokterStore) return;
    dokterStore.setQueryParams({ ORDER_ID: orderId, ALL: 1 });
    dokterStore.load();
  },
});

// RIWAYAT ORDER RESEP - WORKSPACE: pisah grid detil menjadi dua (dokter + farmasi)
Ext.define("layanan.resep.riwayat.WorkspaceOverride", {
  override: "layanan.resep.riwayat.Workspace",
  initComponent: function () {
    var me = this;
    me.callParent(arguments);

    me.on("afterrender", function () {
      var detilGrid = me.down("[reference=riwayatorderresepdetil]");
      if (!detilGrid) return;

      var detilStore = detilGrid.getStore();

      function getAsalOrder(record) {
        var idOD = record.get("ID_ORDER_DETAIL");
        if (idOD && idOD > 0) return true;
        var ref = record.get("REFERENSI") || {};
        return !!ref.ORDER_RESEP;
      }

      // Store terpisah untuk farmasi (ID_ORDER_DETAIL = null)
      var farmasiStore = Ext.create("Ext.data.Store", {
        model: detilStore.getModel(),
      });

      detilStore.on("load", function (store, records) {
        store.filterBy(function (r) {
          return getAsalOrder(r);
        });
        farmasiStore.removeAll();
        if (records) {
          farmasiStore.add(
            records.filter(function (r) {
              return !getAsalOrder(r);
            })
          );
        }
      });

      // Refresh grid Order Dokter setelah detilresepstore selesai load
      // supaya renderer indikator ▲ punya data untuk dibandingkan
      var dokterStore = me.getViewModel().get("detilresepstore");
      if (dokterStore) {
        dokterStore.on("load", function () {
          detilGrid.getView().refresh();
        });
      }

      // Kolom farmasi grid: sama dengan detilGrid tapi tanpa kolom indikator (index 0)
      // detilGrid sudah punya kolom indikator di index 0 dari DetilOverride
      var cols = detilGrid.headerCt.items.items.map(function (c) {
        var cfg = c.initialConfig;
        if (cfg.text === "Nama Obat") {
          cfg = Ext.apply({}, cfg, { flex: 3, minWidth: 180 });
        }
        return cfg;
      });

      // Lepas detilGrid dari Workspace (jangan di-destroy)
      me.remove(detilGrid, false);
      detilGrid.setTitle("Order Dokter");
      detilGrid.flex = 2;

      var eastPanel = me.add({
        region: "east",
        layout: { type: "vbox", align: "stretch" },
        width: 1000,
        collapsible: true,
        split: true,
        border: false,
        header: false,
        items: [
          detilGrid,
          {
            xtype: "panel",
            title: "Disesuaikan Farmasi",
            flex: 1,
            collapsible: true,
            layout: "fit",
            cls: "farmasi-panel-header",
            items: [{ xtype: "grid", store: farmasiStore, columns: cols }],
          },
        ],
      });

      me.updateLayout();

      function setEast40() {
        var w = me.getWidth();
        if (w > 0) {
          eastPanel.setWidth(Math.round(w * 0.5));
          me.updateLayout();
        }
      }

      // Defer supaya layout sudah settle sebelum kita hitung width
      Ext.defer(setEast40, 50);
      me.on("resize", setEast40);
    });
  },
});

// CPPT - COPY ERESEP: tambah keterangan "(disesuaikan farmasi)"
// Flow:
//   1. resepDetil (ORDER_ID) + farmasi (ORDER_ID) dimuat paralel
//   2. farmasi ORDER_ID dipakai untuk dapat KUNJUNGAN depo farmasi
//   3. farmasi KUNJUNGAN depo dimuat → filter JS: ID_ORDER_DETAIL NULL = ditambah farmasi
Ext.define("rekammedis.cppt.FormControllerFarmasiOverride", {
  override: "rekammedis.cppt.FormController",

  onCopyEResep: function (f) {
    var tf = Ext.ComponentQuery.query("[name=PLANNING]")[0],
      vm = this.getViewModel(),
      kunjungan = vm.get("kjgn"),
      resep = vm.get("eResepStore"),
      resepDetil = vm.get("resepDetilStore");

    resep.setQueryParams({ KUNJUNGAN: kunjungan.get("NOMOR"), HISTORY: 1 });
    resep.load(function (data) {
      var rawIds = data.map(function (item) {
        return item.id;
      });
      var idArr = rawIds
        .filter(function (id, idx) {
          return rawIds.indexOf(id) === idx;
        })
        .filter(Boolean);
      var orderId = idArr.pop();

      var selesai = 0,
        hasilDetil,
        depoKunjungan;

      function onKeduaSelesai() {
        selesai++;
        if (selesai < 2) return;

        var nilaiSekarang = tf.getValue() || "";

        // Tulis semua dari order dokter (tanpa suffix)
        hasilDetil.forEach(function (row) {
          var namaObat = row["data"]["REFERENSI"]["FARMASI"]["NAMA"];
          if (nilaiSekarang.indexOf(namaObat) !== -1) return;
          nilaiSekarang +=
            "<br>" +
            namaObat +
            " [" +
            row["data"]["REFERENSI"]["FREKUENSI"]["FREKUENSI"] +
            " " +
            row["data"]["DOSIS"] +
            "]";
        });

        if (!depoKunjungan) {
          // Belum ada data farmasi — tampilkan order dokter saja
          tf.setValue(nilaiSekarang);
          tf.focus();
          return;
        }

        // Step 3: muat semua farmasi untuk kunjungan depo, ambil yang ID_ORDER_DETAIL = null
        var farmasiDepo = Ext.create("data.store.LayananFarmasi", {
          pageSize: 1000,
        });
        farmasiDepo.setQueryParams({ KUNJUNGAN: depoKunjungan });
        farmasiDepo.load(function (dataDepo) {
          dataDepo.forEach(function (row) {
            var idOD = row.get("ID_ORDER_DETAIL");
            if (idOD && idOD > 0) return; // dari order dokter, skip

            var ref = row.get("REFERENSI") || {};
            var namaObat = ref.FARMASI ? ref.FARMASI.NAMA : null;
            if (!namaObat) return;
            if (nilaiSekarang.indexOf(namaObat) !== -1) return;

            var frekRef = ref.FREKUENSI ? ref.FREKUENSI.FREKUENSI : "";
            var dosis = row.get("DOSIS") || "";
            nilaiSekarang +=
              "<br>" +
              namaObat +
              " [" +
              frekRef +
              " " +
              dosis +
              "] (disesuaikan farmasi)";
          });

          tf.setValue(nilaiSekarang);
          tf.focus();
        });
      }

      // Step 1a: muat resepDetil
      resepDetil.setQueryParams({ ORDER_ID: orderId });
      resepDetil.load(function (data) {
        hasilDetil = data;
        onKeduaSelesai();
      });

      // Step 1b: muat farmasi by ORDER_ID untuk dapat KUNJUNGAN depo
      var farmasiOrder = Ext.create("data.store.LayananFarmasi", {
        pageSize: 1000,
      });
      farmasiOrder.setQueryParams({ ORDER_ID: orderId });
      farmasiOrder.load(function (data) {
        if (data.length > 0) {
          depoKunjungan = data[0].get("KUNJUNGAN");
        }
        onKeduaSelesai();
      });
    });
  },
});

// Komponen slide: satu poliklinik ditampilkan per slide, auto-loop tiap 7 detik.
// Dokter di kiri, jam praktik di kanan (hijau jika BUKA, "Libur" merah jika status != BUKA).
Ext.define("antrian.display.JadwalDokterView", {
  extend: "Ext.Component",
  xtype: "antrian-jadwal-dokter-view",
  slideIndex: 0,
  slideIntervalMs: 7000,
  slideTransitionMs: 600,
  apiRefreshIntervalMs: 120 * 1000,
  apiUrl: window.SIMPEL_JADWAL_DOKTER_API_URL,
  autoScroll: false,
  slideTpl: Ext.create(
    "Ext.XTemplate",
    '<div style="padding:15px;box-sizing:border-box;height:100%;overflow:auto;">',
    '<div style="font-size:32px;line-height:1.2;font-weight:bold;color:#ffffff;background-color:#233876;padding:12px 15px;border-radius:6px;margin-bottom:12px;">{poliklinik}</div>',
    '<tpl for="dokter">',
    '<div style="padding:14px 12px;margin-bottom:8px;border-radius:6px;background-color:{[xindex % 2 === 0 ? "rgba(0,0,0,0.05)" : "transparent"]};">',
    '<div style="font-size:28px;line-height:1.2;font-weight:bold;color:#1a1a1a;">{nama}</div>',
    "<tpl if=\"status == 'BUKA'\">",
    '<tpl if="sesuai_perjanjian">',
    '<div style="display:inline-block;font-size:22px;font-weight:600;color:#2e7d32;background-color:#e8f5e9;padding:6px 14px;border-radius:20px;margin-top:10px;">Sesuai Perjanjian</div>',
    "<tpl else>",
    '<div style="display:inline-block;font-size:22px;font-weight:600;color:#2e7d32;background-color:#e8f5e9;padding:6px 14px;border-radius:20px;margin-top:10px;">🕐 {jam_mulai} - {jam_selesai}</div>',
    "</tpl>",
    "<tpl else>",
    '<div style="display:inline-block;font-size:22px;font-weight:600;color:#c62828;background-color:#fdecea;padding:6px 14px;border-radius:20px;margin-top:10px;">Libur</div>',
    "</tpl>",
    "</div>",
    "</tpl>",
    "</div>"
  ),
  afterRender: function () {
    var me = this;
    me.callParent(arguments);
    me.el.setStyle({ position: "relative", overflow: "hidden" });
    if (me.apiUrl) {
      me.loadFromApi();
      me.startApiRefresh();
    }
    me.startLoop();
  },
  setJadwalData: function (payload) {
    var me = this;
    me.jadwal = (payload && payload.data) || [];
    me.slideIndex = 0;
    me.renderSlide();
  },
  loadFromApi: function () {
    var me = this;
    Ext.Ajax.request({
      url: me.apiUrl,
      method: "GET",
      disableCaching: true,
      success: function (response) {
        var data;
        try {
          data = Ext.JSON.decode(response.responseText);
        } catch (e) {
          return;
        }
        me.setJadwalData(data);
      },
      failure: function () {
        // request gagal (mis. server/CORS) - biarkan data yang sedang tampil tetap ada
      },
    });
  },
  startApiRefresh: function () {
    var me = this;
    if (me.apiRefreshTask) {
      return;
    }
    me.apiRefreshTask = {
      run: function () {
        if (!me.rendered || !me.isVisible(true)) {
          return;
        }
        me.loadFromApi();
      },
      interval: me.apiRefreshIntervalMs,
    };
    Ext.TaskManager.start(me.apiRefreshTask);
  },
  renderSlide: function () {
    var me = this;
    if (!me.rendered || !me.jadwal || !me.jadwal.length) {
      return;
    }
    var html = me.slideTpl.apply(me.jadwal[me.slideIndex]);
    var oldSlide = me.currentSlideEl;
    var newSlide = Ext.DomHelper.append(me.el.dom, {
      tag: "div",
      style:
        "position:absolute;top:0;left:0;width:100%;height:100%;box-sizing:border-box;" +
        "transform:translateX(100%);transition:transform " +
        me.slideTransitionMs +
        "ms ease-in-out;",
      html: html,
    });
    // paksa reflow supaya transisi transform benar-benar berjalan
    newSlide.offsetHeight;
    Ext.defer(function () {
      newSlide.style.transform = "translateX(0)";
      if (oldSlide) {
        oldSlide.style.transform = "translateX(-100%)";
      }
    }, 20);
    Ext.defer(function () {
      if (oldSlide && oldSlide.parentNode) {
        oldSlide.parentNode.removeChild(oldSlide);
      }
    }, me.slideTransitionMs + 60);
    me.currentSlideEl = newSlide;
  },
  startLoop: function () {
    var me = this;
    if (me.loopTask) {
      return;
    }
    me.loopTask = {
      run: function () {
        if (!me.jadwal || !me.jadwal.length) {
          return;
        }
        me.slideIndex = (me.slideIndex + 1) % me.jadwal.length;
        me.renderSlide();
      },
      interval: me.slideIntervalMs,
    };
    Ext.TaskManager.start(me.loopTask);
  },
  onDestroy: function () {
    var me = this;
    if (me.loopTask) {
      Ext.TaskManager.stop(me.loopTask);
      me.loopTask = null;
    }
    if (me.apiRefreshTask) {
      Ext.TaskManager.stop(me.apiRefreshTask);
      me.apiRefreshTask = null;
    }
    me.callParent(arguments);
  },
});

// ANTRIAN DISPLAY - REDESIGN WARNA (struktur & layout tidak diubah)
// Primary color: #d606b0, background putih, prioritas keterbacaan nomor antrian.
Ext.define("antrian.display.ViewColorOverride", {
  override: "antrian.display.View",
  tpl: Ext.create(
    "Ext.XTemplate",
    '<tpl for=".">',
    '<div class="thumb-wrap big-33 small-50" style="text-align:center;width:{KOLOM}%;display:grid;padding:15px;">',
    '<a class="thumb" href="#" style="background:#ffffff;border:1px solid #f2d3ec;border-radius:10px;box-shadow:0 2px 8px rgba(214,6,176,0.12);">',
    '<div class="thumb-title-container" style="float:left;width:50%">',
    '<div class="thumb-title"><p style="font-size:22px;color:#d606b0;font-weight:bold;">LOKET</p></div>',
    '<div class="thumb-title">',
    '<p style="font-size:74px;color:#d606b0;font-weight:bold;">{LOKET} </p>',
    "</div>",
    "</div>",
    '<div class="thumb-title-container" style="float:right;width:50%;border-left:2px solid #f2d3ec;">',
    '<div class="thumb-title"><p style="font-size:22px;color:#333333;">ANTRIAN</p></div>',
    '<div class="thumb-title">',
    '<p style="font-size:64px;"><u style="text-decoration:none;color:#d606b0;font-weight:bold;">{POS}{CARA_BAYAR}</u></p><p style="font-size:64px;color:#1a1a1a;font-weight:bold;">{[this.formatNomor(values.NOMOR)]}</p>',
    "</div>",
    "</div>",
    '<div class="thumb-title-container" style="float:left;width:100%;border-top:2px solid #f2d3ec;">',
    '<div class="thumb-title"><p style="font-size:22px;color:{[this.formatColorStatus(values.STATUS)]}">{[this.formatStatus(values.STATUS)]}</p></div>',
    "</div>",
    "</a>",
    "</div>",
    "</tpl>",
    {
      formatNomor: function (a) {
        var b = Ext.String.leftPad(a, 3, "0");
        return b;
      },
      formatStatus: function (a) {
        if (a == 1) {
          return "Buka";
        }
        return "Tutup";
      },
      formatColorStatus: function (a) {
        if (a == 1) {
          return "#2e7d32";
        }
        return "#aaaaaa";
      },
    }
  ),
});

// ANTRIAN DISPLAY WORKSPACE - REDESIGN WARNA (struktur & layout tidak diubah)
// Hanya mengganti nilai warna (gradient header/footer, panel kiri, judul kanan)
// menjadi tema primary #d606b0 + background putih. Logika WebSocket & panggilan tetap sama.
Ext.define("antrian.display.WorkspaceColorOverride", {
  override: "antrian.display.Workspace",
  bodyStyle: "background-color:#ffffff",
  initComponent: function () {
    if (window.location.protocol == "http:") {
      var d = "ws";
    } else {
      var d = "wss";
    }
    var b = this;
    var a = Ext.create("Ext.ux.WebSocket", {
      url: "ws://" + window.location.hostname + ":8899",
      listeners: {
        open: function (e) {
          b.getViewModel().set("statusWebsocket", "Connected");
        },
        message: function (e, h) {
          var f = JSON.parse(h);
          if (f) {
            if (f.act) {
              if (f.act == "PANGGIL") {
                if (b.getViewModel().get("posAntrian") == f.pos) {
                  var g =
                    f.pos + "" + f.carabayar + "" + f.nomor + "" + f.loket;
                  if (!b.dataAntrian.includes(g)) {
                    b.datapanggil.push(f);
                    b.dataAntrian.push(g);
                  }
                  if (b.datapanggil.length === 1) {
                    b.setProsesPanggil();
                    b.onRefreshView(f.pos);
                  }
                }
              }
              if (f.act == "REFRESH_LOKET") {
                if (b.getViewModel().get("posAntrian") == f.pos) {
                  b.onRefreshView(f.pos);
                }
              }
            }
          }
        },
        close: function (e) {
          b.getViewModel().set("statusWebsocket", "Disonnected Socket");
        },
      },
    });
    var PRIMARY = "#d606b0";
    var PRIMARY_DARK = "#a8058e";
    var PRIMARY_SOFT = "#fbe9f6";
    var BORDER_SOFT = "#f2d3ec";
    var JAM_BIRU = "#233876";
    // Biru soft senada dengan JAM_BIRU (header) untuk background panel jadwal dokter
    var BIRU_SOFT = "#eaedf4";
    b.items = [
      {
        layout: { type: "hbox", align: "middle" },
        border: false,
        height: 50,
        bodyStyle:
          "padding-left:10px;background-color:" +
          JAM_BIRU +
          ";border-bottom:2px solid " +
          PRIMARY +
          ";",
        items: [
          {
            xtype: "container",
            flex: 1,
            border: false,
            layout: { type: "hbox", align: "middle" },
            items: [
              {
                xtype: "image",
                bind: { src: "classic/resources/images/{instansi}.png" },
                id: "idImage",
                width: 40,
                border: true,
                style: "border-radius:4px;border-color:" + BORDER_SOFT + ";",
                bodyStyle: "background-color:" + PRIMARY_SOFT + ";",
              },
              {
                flex: 1,
                bind: { data: { items: "{store.data.items}" } },
                tpl: new Ext.XTemplate(
                  '<tpl for="items">',
                  "{data.REFERENSI.PPK.NAMA}",
                  "</tpl>"
                ),
                border: false,
                bodyStyle:
                  "background-color:transparent; font-size: 18px; font-weight:bold; color: #ffffff; padding-left:10px;",
              },
            ],
          },
          {
            xtype: "container",
            width: 350,
            border: false,
            layout: { type: "hbox", pack: "center", align: "middle" },
            items: [
              {
                xtype: "component",
                bind: { html: "{tglNow}" },
                style:
                  "background-color:transparent; font-size: 20px; font-weight:bold; color: #ffffff;",
              },
            ],
          },
          {
            xtype: "container",
            flex: 1,
            border: false,
          },
        ],
      },
      {
        flex: 1,
        layout: { type: "hbox", align: "stretch" },
        defaults: { flex: 1, margin: "0 1 0 1" },
        border: false,
        reference: "informasi",
        items: [
          {
            flex: 1,
            border: false,
            layout: { type: "vbox", align: "stretch" },
            defaults: { bodyStyle: "background-color:" + PRIMARY_SOFT },
            items: [
              /*
              {
                border: true,
                style:
                  "padding:15px;background-color:" +
                  PRIMARY_SOFT +
                  ";border-bottom:1px solid " +
                  BORDER_SOFT +
                  ";",
                bodyStyle: "background-color:transparent",
                html: '<iframe width="100%" height="300px" src="classic/resources/images/banner-antrian/video.mp4" frameborder="0" allow="accelerometer loop="true" autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>',
              },
              */
              {
                xtype: "image",
                src: "classic/resources/images/banner-antrian/image.jpg",
                style:
                  "width:100%;height:300px;padding:15px;background-color:" +
                  PRIMARY_SOFT +
                  ";border-bottom:1px solid " +
                  BORDER_SOFT +
                  ";",
                border: true,
                bodyStyle: "background-color:transparent;",
              },
              {
                xtype: "container",
                style: "background-color:" + PRIMARY_SOFT + ";",
                flex: 1,
              },
              {
                style:
                  "padding:10px;font-size:14px;border-top:1px solid " +
                  BORDER_SOFT +
                  ";text-left:center;font-style:italic;color:#666666;background-color:" +
                  PRIMARY_SOFT +
                  ";",
                bodyStyle: "background-color:transparent",
                bind: { html: "Status : {statusWebsocket}" },
              },
            ],
          },
          {
            flex: 4,
            border: false,
            layout: { type: "vbox", align: "stretch" },
            bodyPadding: "20",
            items: [
              {
                xtype: "component",
                html: "NOMOR ANTRIAN YANG DI LAYANI",
                style:
                  "padding:15px;font-size:22px;text-align:center;color:" +
                  PRIMARY +
                  ";font-weight:bold;background-color:" +
                  PRIMARY_SOFT +
                  ";border-radius:4px;margin-top:20px",
              },
              {
                flex: 1,
                reference: "dataview",
                style: "margin-top:10px;background-color:#FFF",
                xtype: "antrian-display-view",
                viewConfig: { loadMask: false },
              },
            ],
          },
          {
            flex: 2,
            border: false,
            layout: { type: "vbox", align: "stretch" },
            bodyPadding: "20",
            items: [
              {
                xtype: "component",
                html: "JADWAL PRAKTEK DOKTER HARI INI",
                style:
                  "padding:10px 15px;font-size:19px;text-align:center;color:#1a1a1a;font-weight:bold;margin-top:20px",
              },
              {
                flex: 1,
                reference: "jadwaldokter",
                xtype: "antrian-jadwal-dokter-view",
                apiUrl: window.SIMPEL_JADWAL_DOKTER_API_URL,
                style:
                  "margin-top:10px;background-color:" +
                  BIRU_SOFT +
                  ";border-radius:8px;",
              },
            ],
          },
        ],
      },
      {
        layout: { type: "hbox", align: "middle" },
        height: 30,
        border: false,
        bodyStyle:
          "background: -webkit-gradient(linear, left top, left bottom, color-stop(0%," +
          PRIMARY +
          "), color-stop(100%," +
          PRIMARY_DARK +
          "));",
        items: [
          {
            xtype: "displayfield",
            flex: 1,
            fieldStyle:
              "background-color:transparent;font-size: 14px;  margin-left: 10px;color: white;",
            border: false,
            bind: { value: "<marquee>{infoTeks}</marquee>" },
          },
        ],
      },
    ];
    // callSuper (bukan callParent): melewati initComponent asli Workspace
    // (yang akan menimpa ulang b.items dengan warna lama), langsung ke com.Form.
    b.callSuper(arguments);
  },
});