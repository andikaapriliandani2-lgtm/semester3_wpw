const app = document.getElementById("posApp");

if (app) {
    const searchInput = document.getElementById("searchProduct");
    const results = document.getElementById("productResults");
    const cartBody = document.getElementById("cartBody");
    const paymentInput = document.getElementById("payment");
    const heldStorageKey = "minimarket-held-transactions";
    const checkoutDraftKey = "minimarket-checkout-draft";
    let cart = [];
    let searchedProducts = new Map();
    let paymentMethod = document.getElementById("paymentMethodInput").value;
    let searchTimer;

    const rupiah = (value) =>
        new Intl.NumberFormat("id-ID", {
            style: "currency",
            currency: "IDR",
            maximumFractionDigits: 0,
        }).format(Math.round(Number(value) || 0));

    const escapeHtml = (value) =>
        String(value ?? "").replace(
            /[&<>"']/g,
            (character) =>
                ({
                    "&": "&amp;",
                    "<": "&lt;",
                    ">": "&gt;",
                    '"': "&quot;",
                    "'": "&#039;",
                })[character],
        );

    const totals = () => {
        const subtotal = cart.reduce(
            (sum, item) => sum + item.price * item.quantity,
            0,
        );
        const percent = Math.min(
            100,
            Math.max(
                0,
                Number(document.getElementById("discountPercent").value) || 0,
            ),
        );
        const discount = Math.min(
            subtotal,
            Math.round((subtotal * percent) / 100) +
                Math.max(
                    0,
                    Number(document.getElementById("discountAmount").value) ||
                        0,
                ),
        );
        const tax = Math.max(
            0,
            Number(document.getElementById("tax").value) || 0,
        );
        const fee = Math.max(
            0,
            Number(document.getElementById("otherFee").value) || 0,
        );
        return {
            subtotal,
            discount,
            grandTotal: Math.max(0, subtotal - discount + tax + fee),
        };
    };

    const updatePayment = () => {
        const { grandTotal } = totals();
        const payment = Math.max(0, Number(paymentInput.value) || 0);
        const difference = payment - grandTotal;
        const changeBox = document.getElementById("changeBox");
        changeBox.classList.toggle("short", difference < 0);
        document.getElementById("changeLabel").textContent =
            difference < 0 ? "UANG KURANG" : "KEMBALIAN";
        document.getElementById("change").textContent = rupiah(
            Math.abs(difference),
        );

        const quickCash = document.getElementById("quickCash");
        const candidates = [
            ...new Set([
                Math.ceil(grandTotal / 10000) * 10000,
                Math.ceil(grandTotal / 50000) * 50000,
                Math.ceil(grandTotal / 100000) * 100000,
            ]),
        ].filter((amount) => amount >= grandTotal);
        quickCash.innerHTML =
            grandTotal > 0
                ? `${candidates.map((amount) => `<button type="button" data-cash="${amount}">${rupiah(amount)}</button>`).join("")}<button type="button" data-cash="${grandTotal}">Uang pas</button>`
                : "";
    };

    const renderCart = () => {
        document.getElementById("itemCount").textContent =
            `${cart.reduce((sum, item) => sum + item.quantity, 0)} Item`;
        document.getElementById("totalQty").textContent = cart.reduce(
            (sum, item) => sum + item.quantity,
            0,
        );

        if (cart.length === 0) {
            cartBody.innerHTML =
                '<tr><td colspan="6" class="pos-empty">Keranjang masih kosong.<br>Silakan cari atau scan barang.</td></tr>';
        } else {
            cartBody.innerHTML = cart
                .map(
                    (item, index) => `
                <tr>
                    <td>${index + 1}</td>
                    <td><div class="pos-product-name">${escapeHtml(item.name)}</div><div class="pos-product-code">${escapeHtml(item.code || item.barcode || "Tanpa kode")}</div></td>
                    <td class="text-right">${rupiah(item.price)}</td>
                    <td><div class="pos-qty"><button type="button" data-action="decrease" data-id="${item.id}" aria-label="Kurangi ${escapeHtml(item.name)}">−</button><input type="number" min="1" max="${item.stock}" value="${item.quantity}" data-action="quantity" data-id="${item.id}" aria-label="Jumlah ${escapeHtml(item.name)}"><button type="button" data-action="increase" data-id="${item.id}" aria-label="Tambah ${escapeHtml(item.name)}">+</button></div></td>
                    <td class="text-right font-weight-bold">${rupiah(item.price * item.quantity)}</td>
                    <td><button type="button" class="pos-remove" data-action="remove" data-id="${item.id}" aria-label="Hapus ${escapeHtml(item.name)}"><i class="fas fa-trash-alt" aria-hidden="true"></i></button></td>
                </tr>`,
                )
                .join("");
        }

        const { subtotal, grandTotal } = totals();
        document.getElementById("subtotal").textContent = rupiah(subtotal);
        document.getElementById("grandTotal").textContent = rupiah(grandTotal);
        updatePayment();
    };

    const addProduct = (product) => {
        const existing = cart.find((item) => item.id === product.id);
        if (existing) {
            if (existing.quantity >= product.stock) {
                window.alert(`Stok ${product.name} hanya ${product.stock}.`);
                return;
            }
            existing.quantity += 1;
        } else {
            cart.push({
                id: product.id,
                name: product.name,
                code: product.code,
                barcode: product.barcode,
                price: Number(product.price),
                stock: Number(product.stock),
                quantity: 1,
            });
        }
        searchInput.value = "";
        results.style.display = "none";
        renderCart();
        searchInput.focus();
    };

    const searchProducts = async () => {
        const keyword = searchInput.value.trim();
        if (!keyword) {
            results.style.display = "none";
            return;
        }

        try {
            const response = await fetch(
                `${app.dataset.searchUrl}?q=${encodeURIComponent(keyword)}`,
                {
                    headers: { Accept: "application/json" },
                },
            );
            if (!response.ok) throw new Error("Pencarian produk gagal.");
            const { products } = await response.json();
            searchedProducts = new Map(
                products.map((product) => [product.id, product]),
            );
            results.innerHTML = products.length
                ? products
                      .map(
                          (product) =>
                              `<div class="pos-result" role="option" tabindex="0" data-product-id="${product.id}"><div><div class="pos-result-name">${escapeHtml(product.name)}</div><div class="pos-result-code">${escapeHtml(product.code || "Tanpa SKU")} · ${escapeHtml(product.barcode || "Tanpa barcode")} · Stok ${product.stock}</div></div><div class="pos-result-price">${rupiah(product.price)}</div></div>`,
                      )
                      .join("")
                : '<div class="pos-result text-muted">Barang tidak ditemukan</div>';
            results.style.display = "block";

            const exact = products.find(
                (product) =>
                    product.barcode === keyword || product.code === keyword,
            );
            if (exact && document.activeElement !== searchInput)
                addProduct(exact);
        } catch (error) {
            results.innerHTML = `<div class="pos-result text-danger">${escapeHtml(error.message)}</div>`;
            results.style.display = "block";
        }
    };

    const readHeldTransactions = () => {
        try {
            return JSON.parse(localStorage.getItem(heldStorageKey) || "[]");
        } catch {
            return [];
        }
    };

    const renderHeldTransactions = () => {
        const held = readHeldTransactions();
        document.getElementById("heldCount").textContent = held.length;
        const list = document.getElementById("heldList");
        list.innerHTML = held.length
            ? held
                  .map(
                      (item) =>
                          `<div class="pos-held-item"><span>${escapeHtml(item.customerName)} · ${item.cart.reduce((sum, product) => sum + product.quantity, 0)} item · ${rupiah(item.cart.reduce((sum, product) => sum + product.price * product.quantity, 0))}</span><span><button class="btn btn-sm btn-outline-success" type="button" data-resume="${item.id}" title="Lanjutkan transaksi"><i class="fas fa-play" aria-hidden="true"></i></button> <button class="btn btn-sm btn-outline-danger" type="button" data-delete-held="${item.id}" title="Hapus transaksi ditahan"><i class="fas fa-trash-alt" aria-hidden="true"></i></button></span></div>`,
                  )
                  .join("")
            : '<div class="py-3 small text-muted">Belum ada transaksi yang ditahan.</div>';
    };

    const resetForm = () => {
        cart = [];
        document.getElementById("customerName").value = "Umum";
        document.getElementById("customerPhone").value = "";
        document.getElementById("discountPercent").value = "0";
        document.getElementById("discountAmount").value = "0";
        document.getElementById("tax").value = "0";
        document.getElementById("otherFee").value = "0";
        paymentInput.value = "0";
        paymentMethod = "Tunai";
        document.getElementById("paymentMethodInput").value = paymentMethod;
        document.querySelectorAll(".pos-method").forEach((button) => {
            const active = button.dataset.method === paymentMethod;
            button.classList.toggle("active", active);
            button.setAttribute("aria-pressed", String(active));
        });
        renderCart();
    };

    const renderDate = () => {
        document.getElementById("currentDate").textContent =
            new Date().toLocaleString("id-ID");
    };

    document
        .getElementById("searchButton")
        .addEventListener("click", searchProducts);
    searchInput.addEventListener("input", () => {
        window.clearTimeout(searchTimer);
        searchTimer = window.setTimeout(searchProducts, 180);
    });
    searchInput.addEventListener("keydown", (event) => {
        if (event.key === "Enter") {
            event.preventDefault();
            searchProducts().then(() => {
                const first = results.querySelector("[data-product-id]");
                if (first) first.click();
            });
        }
    });
    results.addEventListener("click", (event) => {
        const result = event.target.closest("[data-product-id]");
        if (!result) return;
        const product = searchedProducts.get(Number(result.dataset.productId));
        if (product) addProduct(product);
    });
    cartBody.addEventListener("click", (event) => {
        const button = event.target.closest("[data-action]");
        if (!button) return;
        const item = cart.find(
            (entry) => entry.id === Number(button.dataset.id),
        );
        if (!item) return;
        if (button.dataset.action === "remove")
            cart = cart.filter((entry) => entry !== item);
        if (button.dataset.action === "increase" && item.quantity < item.stock)
            item.quantity += 1;
        if (button.dataset.action === "decrease") item.quantity -= 1;
        if (item.quantity <= 0) cart = cart.filter((entry) => entry !== item);
        renderCart();
    });
    cartBody.addEventListener("change", (event) => {
        if (event.target.dataset.action !== "quantity") return;
        const item = cart.find(
            (entry) => entry.id === Number(event.target.dataset.id),
        );
        if (item)
            item.quantity = Math.max(
                1,
                Math.min(item.stock, Number(event.target.value) || 1),
            );
        renderCart();
    });
    document
        .querySelectorAll("#discountPercent, #discountAmount, #tax, #otherFee")
        .forEach((input) => input.addEventListener("input", renderCart));
    paymentInput.addEventListener("input", updatePayment);
    document.getElementById("quickCash").addEventListener("click", (event) => {
        const button = event.target.closest("[data-cash]");
        if (!button) return;
        paymentInput.value = button.dataset.cash;
        updatePayment();
    });
    document.querySelectorAll(".pos-method").forEach((button) =>
        button.addEventListener("click", () => {
            paymentMethod = button.dataset.method;
            document.getElementById("paymentMethodInput").value = paymentMethod;
            document.querySelectorAll(".pos-method").forEach((methodButton) => {
                const active = methodButton === button;
                methodButton.classList.toggle("active", active);
                methodButton.setAttribute("aria-pressed", String(active));
            });
            if (paymentMethod !== "Tunai")
                paymentInput.value = totals().grandTotal;
            updatePayment();
        }),
    );
    document.getElementById("holdButton").addEventListener("click", () => {
        if (cart.length === 0) {
            window.alert("Tidak ada transaksi untuk ditahan.");
            return;
        }
        const held = readHeldTransactions();
        held.unshift({
            id: `${Date.now()}-${Math.random().toString(36).slice(2, 7)}`,
            cart,
            customerName:
                document.getElementById("customerName").value || "Umum",
            customerPhone: document.getElementById("customerPhone").value,
            discountPercent: document.getElementById("discountPercent").value,
            discountAmount: document.getElementById("discountAmount").value,
            tax: document.getElementById("tax").value,
            otherFee: document.getElementById("otherFee").value,
        });
        localStorage.setItem(heldStorageKey, JSON.stringify(held));
        resetForm();
        renderHeldTransactions();
        document.getElementById("heldList").style.display = "block";
    });
    document.getElementById("heldToggle").addEventListener("click", (event) => {
        const list = document.getElementById("heldList");
        const open = list.style.display !== "block";
        list.style.display = open ? "block" : "none";
        event.currentTarget.setAttribute("aria-expanded", String(open));
    });
    document.getElementById("heldList").addEventListener("click", (event) => {
        const resumeButton = event.target.closest("[data-resume]");
        const deleteButton = event.target.closest("[data-delete-held]");
        const held = readHeldTransactions();
        if (deleteButton) {
            localStorage.setItem(
                heldStorageKey,
                JSON.stringify(
                    held.filter(
                        (item) => item.id !== deleteButton.dataset.deleteHeld,
                    ),
                ),
            );
            renderHeldTransactions();
            return;
        }
        if (resumeButton) {
            if (
                cart.length > 0 &&
                !window.confirm(
                    "Transaksi saat ini akan ditahan sebelum melanjutkan. Lanjutkan?",
                )
            )
                return;
            const selected = held.find(
                (item) => item.id === resumeButton.dataset.resume,
            );
            if (!selected) return;
            if (cart.length > 0) document.getElementById("holdButton").click();
            cart = selected.cart;
            document.getElementById("customerName").value =
                selected.customerName;
            document.getElementById("customerPhone").value =
                selected.customerPhone;
            document.getElementById("discountPercent").value =
                selected.discountPercent;
            document.getElementById("discountAmount").value =
                selected.discountAmount;
            document.getElementById("tax").value = selected.tax;
            document.getElementById("otherFee").value = selected.otherFee;
            localStorage.setItem(
                heldStorageKey,
                JSON.stringify(
                    readHeldTransactions().filter(
                        (item) => item.id !== selected.id,
                    ),
                ),
            );
            renderHeldTransactions();
            renderCart();
        }
    });
    document.getElementById("cancelButton").addEventListener("click", () => {
        if (cart.length > 0 && !window.confirm("Batalkan transaksi ini?"))
            return;
        resetForm();
    });
    document
        .getElementById("checkoutForm")
        .addEventListener("submit", (event) => {
            if (cart.length === 0) {
                event.preventDefault();
                window.alert("Keranjang masih kosong.");
                return;
            }
            if ((Number(paymentInput.value) || 0) < totals().grandTotal) {
                event.preventDefault();
                window.alert(
                    "Uang pembayaran masih kurang dari total transaksi.",
                );
                paymentInput.focus();
                return;
            }
            const itemsContainer = document.getElementById("checkoutItems");
            itemsContainer.replaceChildren();
            cart.forEach((item, index) => {
                for (const [field, value] of [
                    ["product_id", item.id],
                    ["quantity", item.quantity],
                ]) {
                    const input = document.createElement("input");
                    input.type = "hidden";
                    input.name = `items[${index}][${field}]`;
                    input.value = value;
                    itemsContainer.append(input);
                }
            });
            sessionStorage.setItem(checkoutDraftKey, JSON.stringify(cart));
        });
    document.addEventListener("keydown", (event) => {
        if (event.key === "F2") {
            event.preventDefault();
            searchInput.focus();
        }
        if (event.key === "F4") {
            event.preventDefault();
            paymentInput.focus();
        }
        if (event.key === "Escape") results.style.display = "none";
    });
    document.addEventListener("click", (event) => {
        if (!event.target.closest(".pos-search-row"))
            results.style.display = "none";
    });

    if (app.dataset.hasErrors === "1") {
        try {
            cart = JSON.parse(sessionStorage.getItem(checkoutDraftKey) || "[]");
        } catch {
            cart = [];
        }
        sessionStorage.removeItem(checkoutDraftKey);
    }

    renderDate();
    renderHeldTransactions();
    renderCart();
}
