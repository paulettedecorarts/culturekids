<div>
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:var(--sp-5);flex-wrap:wrap;gap:var(--sp-3)">
        <div>
            <div class="sa-page-title">Branding & Themes</div>
            <div class="sa-breadcrumb">Platform · UI customization & Organization-specific branding</div>
        </div>
        <div style="display:flex;gap:var(--sp-2);align-items:center">
            <button class="btn btn-primary btn-sm" style="background:var(--banana-green); border:none; color:#fff; padding: var(--sp-2) var(--sp-4); border-radius: var(--r-full); font-weight:700; cursor:pointer;">Save All Changes</button>
        </div>
    </div>

    <!-- Active Selection -->
    <div style="background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.1); border-radius:var(--r-xl); padding:var(--sp-4); margin-bottom:var(--sp-6); display:flex; align-items:center; gap:var(--sp-4)">
        <div style="font-size:12px; font-weight:700; color:var(--savanna-gold); text-transform:uppercase;">Configuring For:</div>
        <select style="background:rgba(0,0,0,.2); border:1px solid rgba(255,255,255,.1); border-radius:var(--r-full); padding:var(--sp-2) var(--sp-4); color:#fff; font-size:14px; outline:none; font-weight:700; flex:1">
            <option>Global Default (PCK Standard)</option>
            <option>Kampala Model School</option>
            <option>UNICEF Uganda Pilot</option>
            <option>Heritage Learning Centre</option>
        </select>
    </div>

    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:var(--sp-6);">
        <!-- Color Palette -->
        <div style="background:rgba(255,255,255,.03); border:1px solid rgba(255,255,255,.07); border-radius:var(--r-2xl); padding:var(--sp-6);">
            <h3 style="font-size:16px; font-weight:800; color:#fff; margin-bottom:var(--sp-5); font-family:var(--font-display);">Organization Palette</h3>
            
            <div style="display:grid; gap:var(--sp-4);">
                <div style="display:flex; align-items:center; justify-content:space-between">
                    <div>
                        <div style="font-size:13px; font-weight:600; color:#fff">Primary Brand Color</div>
                        <div style="font-size:11px; color:rgba(255,255,255,.4)">Main action & primary branding</div>
                    </div>
                    <input type="color" value="#C44B2B" style="width:40px; height:40px; border-radius:8px; border:none; background:none; cursor:pointer;">
                </div>

                <div style="display:flex; align-items:center; justify-content:space-between">
                    <div>
                        <div style="font-size:13px; font-weight:600; color:#fff">Secondary Color</div>
                        <div style="font-size:11px; color:rgba(255,255,255,.4)">Sub-sections & secondary buttons</div>
                    </div>
                    <input type="color" value="#E8872A" style="width:40px; height:40px; border-radius:8px; border:none; background:none; cursor:pointer;">
                </div>

                <div style="display:flex; align-items:center; justify-content:space-between">
                    <div>
                        <div style="font-size:13px; font-weight:600; color:#fff">Accent Color</div>
                        <div style="font-size:11px; color:rgba(255,255,255,.4)">Success states & progress bars</div>
                    </div>
                    <input type="color" value="#4A7C59" style="width:40px; height:40px; border-radius:8px; border:none; background:none; cursor:pointer;">
                </div>
            </div>
        </div>

        <!-- Branding & Logo -->
        <div style="background:rgba(255,255,255,.03); border:1px solid rgba(255,255,255,.07); border-radius:var(--r-2xl); padding:var(--sp-6);">
            <h3 style="font-size:16px; font-weight:800; color:#fff; margin-bottom:var(--sp-5); font-family:var(--font-display);">Logo & Assets</h3>
            
            <div style="display:grid; gap:var(--sp-5);">
                <div>
                    <label style="display:block; font-size:11px; font-weight:700; color:rgba(255,255,255,.5); margin-bottom:8px; text-transform:uppercase;">App Shell Logo</label>
                    <div style="border:2px dashed rgba(255,255,255,.1); border-radius:var(--r-xl); padding:var(--sp-8); text-align:center; transition:all .2s; cursor:pointer;" onmouseover="this.style.borderColor='rgba(255,255,255,.3)'" onmouseout="this.style.borderColor='rgba(255,255,255,.1)'">
                        <span style="font-size:32px; display:block; margin-bottom:10px">🖼️</span>
                        <span style="font-size:12px; color:rgba(255,255,255,.4)">Drop logo here (JPG, PNG, SVG)</span>
                    </div>
                </div>

                <div>
                    <label style="display:block; font-size:11px; font-weight:700; color:rgba(255,255,255,.5); margin-bottom:8px; text-transform:uppercase;">Custom Font Family</label>
                    <select style="width:100%; background:rgba(0,0,0,.2); border:1px solid rgba(255,255,255,.1); border-radius:var(--r-full); padding:var(--sp-3) var(--sp-4); color:#fff; font-size:13px; outline:none;">
                        <option>Inter (Default)</option>
                        <option>Outfit (Modern Display)</option>
                        <option>Nunito (Kids-Friendly)</option>
                        <option>Montserrat (Geometric)</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Live Preview -->
    <div style="margin-top:var(--sp-6);">
        <h3 style="font-size:14px; font-weight:800; color:rgba(255,255,255,.5); margin-bottom:var(--sp-3); font-family:var(--font-display); text-transform:uppercase;">Live Mobile Preview</h3>
        <div style="background:#fff; border-radius:var(--r-3xl); overflow:hidden; width:100%; max-width:800px; height:200px; display:flex; border:8px solid #000; box-shadow:var(--shadow-xl)">
             <div style="width:200px; background:#f4f1ea; padding:20px; border-right:1px solid #ddd">
                <div style="font-weight:900; font-size:14px; color:#C44B2B; margin-bottom:20px">PAULETTE</div>
                <div style="height:10px; width:120px; background:#ddd; border-radius:4px; margin-bottom:10px"></div>
                <div style="height:10px; width:100px; background:#eee; border-radius:4px; margin-bottom:10px"></div>
             </div>
             <div style="flex:1; padding:20px; background:#ffffff; color:#333">
                <div style="font-size:18px; font-weight:800; margin-bottom:15px">Sample UI Module</div>
                <div style="display:flex; gap:10px; margin-bottom:20px">
                    <div style="padding:8px 20px; background:#C44B2B; color:#fff; border-radius:999px; font-size:12px; font-weight:700">Primary CTA</div>
                    <div style="padding:8px 20px; background:#E8872A; color:#fff; border-radius:999px; font-size:12px; font-weight:700">Secondary</div>
                </div>
                <div style="height:6px; background:#eee; border-radius:10px; overflow:hidden">
                    <div style="width:65%; height:100%; background:#4A7C59"></div>
                </div>
                <div style="font-size:10px; color:#999; margin-top:5px">Learning Progress (65%)</div>
             </div>
        </div>
    </div>
</div>
