@extends('layouts.app')

@section('title', ($portfolio->exists ? 'Edit' : 'Create') . ' portfolio | Folio Maker')

@section('content')
  <style id="generator-styles">
    .generator-page {
      color-scheme: light;
      --bg: #f4f3f0;
      --text: #20211f;
      --muted: #62665f;
      --card: #fff;
      --surface: #f8f8f6;
      --line: #d9ddd6;
      --accent: #436747;
      --accent-contrast: #fff;
      --error: #a83228;
      --danger-hover: #a83228;
      --shadow: 0 12px 38px rgb(36 37 32 / 7%);
      --editor-width: 390px;
      --radius: 14px;
      --transition: background-color .3s ease, color .3s ease, border-color .3s ease;
    }
    html[data-theme="dark"] .generator-page {
      color-scheme: dark;
      --bg: #171916;
      --text: #eeefe9;
      --muted: #b8beb4;
      --card: #222520;
      --surface: #292c26;
      --line: #4b5149;
      --accent: #a9d1aa;
      --accent-contrast: #152018;
      --error: #ff938a;
      --danger-hover: #ff938a;
      --shadow: 0 12px 38px rgb(0 0 0 / 24%);
    }
    * { box-sizing: border-box; }
    .generator-page, .generator-page button, .generator-page input, .generator-page textarea, .generator-page select { font-family: "DM Sans", sans-serif; }
    .generator-page { min-height: calc(100vh - 76px); background: transparent; color: var(--text); transition: var(--transition); }
    .generator-page button { color: inherit; }
    .icon-button, .generator-page .button {
      min-height: 38px; border: 1px solid var(--line); border-radius: 10px;
      background: var(--card); cursor: pointer; transition: var(--transition);
    }
    .icon-button { width: 40px; display: grid; place-items: center; font-size: 17px; }
    .generator-page .button { padding: 0 14px; font-size: 12px; font-weight: 700; }
    .generator-page .button:hover, .icon-button:hover { border-color: var(--accent); }
    .button-primary { background: var(--accent); color: var(--accent-contrast); border-color: var(--accent); }
    .button-quiet { background: transparent; }
    .generator-status { color:var(--accent); font-size:11px; font-weight:600; }
    .generator-status:empty { display:none; }
    .generator-status.status-error { color:var(--error); }
    .mobile-switch { display:none; }
    .workspace {
      height: calc(100vh - 76px);
      min-height: 0;
      display: grid;
      grid-template-columns: minmax(320px, var(--editor-width)) minmax(0, 1fr);
    }
    .editor {
      padding: 18px 18px 38px;
      border-right: 1px solid var(--line);
      overflow-y: auto;
      min-height: 0;
      background: var(--bg);
      transition: var(--transition);
    }
    .generator-page .editor-heading { margin: 0 2px 18px; }
    .generator-page .editor-heading h1 { margin:0; font:700 24px "DM Sans",sans-serif; letter-spacing:-.055em; }
    .eyebrow { margin: 0 0 7px; color: var(--accent); text-transform: uppercase; letter-spacing: .15em; font-size: 9px; font-weight: 700; }
    h1 { margin: 0; font-size: 24px; letter-spacing: -.055em; }
    .subheading { margin: 7px 0 0; color: var(--muted); font-size: 12px; line-height: 1.55; }
    .generator-page .editor-section {
      display:block; margin:0; padding:0 16px; border:1px solid var(--line); border-left:3px solid var(--line);
      border-radius:12px; background:var(--card); transition:border-color .2s ease, background-color .2s ease;
    }
    .generator-page .editor-section[open] { border-color:color-mix(in srgb, var(--accent) 42%, var(--line)); border-left-color:var(--accent); }
    .accordion summary { display:block; padding:15px 0; cursor:pointer; list-style:none; }
    .accordion summary::-webkit-details-marker { display:none; }
    .accordion summary:focus-visible { outline:2px solid var(--accent); outline-offset:3px; border-radius:5px; }
    .accordion summary::after { content:"+"; float:right; color:var(--muted); font-size:17px; }
    .accordion[open] summary::after { content:"−"; }
    .accordion summary .section-title { margin:0; }
    .accordion[open] summary { margin-bottom:14px; border-bottom:1px solid var(--line); }
    .accordion-content { max-height:2000px; padding-bottom:16px; overflow:hidden; opacity:1; transition:max-height .2s ease, padding-bottom .2s ease, opacity .2s ease; }
    .accordion:not([open]) > .accordion-content { display:block; max-height:0; padding-bottom:0; opacity:0; }
    .editor-sections { display:grid; gap:13px; }
    .subsection-heading { margin:16px 0 8px; font-size:11px; }
    .editor-actions { padding-top:4px; display:grid; gap:8px; }
    .save-button { width:100%; min-height:42px; }
    .generator-page .save-button {
      min-height:46px; color:var(--accent-contrast) !important; background:var(--accent); border-color:var(--accent);
      font-size:14px; font-weight:700; letter-spacing:.01em;
    }
    .generator-page .save-button:hover { filter:brightness(.94); }
    .current-image:empty { display:none; }
    .current-image img { max-width:130px; max-height:80px; object-fit:cover; border-radius:8px; }
    .current-image button { display:block; margin:4px 0 10px; border:0; color:var(--muted); background:none; cursor:pointer; font-size:10px; }
    .validation-error { color:var(--error); font-size:10px; margin:4px 0 8px; }
    .generator-page .editor { display:block; margin:0; border-radius:0; }
    .generator-page .portfolio { --shadow: 0 12px 38px rgb(36 37 32 / 7%); color:var(--portfolio-ink); }
    .generator-page .section-title { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0; gap:8px; }
    .generator-page .section-title h2 { margin: 0; font:700 12px "DM Sans",sans-serif; letter-spacing: -.01em; }
    .generator-page .section-number { padding:0; color: var(--muted); font: 10px "Roboto Mono", monospace; }
    .field { display: block; margin: 0 0 15px; }
    .field:last-child { margin-bottom: 0; }
    .field-label, .field > span:first-child { display: block; margin-bottom: 6px; font-size: 10px; font-weight: 700; }
    .full-name-field input { font-size: 10px; }
    input[type="text"], input[type="email"], input[type="tel"], input[type="url"], textarea, select {
      width: 100%; min-height: 37px; border: 1px solid var(--line); border-radius: 8px;
      background: var(--card); color: var(--text); outline: none; padding: 9px 10px; font-size: 11px;
      transition: var(--transition);
    }
    .generator-page input::placeholder, .generator-page textarea::placeholder { color:var(--muted); opacity:1; }
    .generator-page select option { color:var(--text); background:var(--card); }
    textarea { min-height: 74px; resize: vertical; line-height: 1.5; }
    input:focus, textarea:focus, select:focus { border-color: var(--accent); box-shadow: 0 0 0 3px color-mix(in srgb, var(--accent) 15%, transparent); }
    input[type="range"] { accent-color: var(--accent); width: 100%; margin: 8px 0 2px; }
    input[type="color"] { width: 100%; height: 36px; padding: 3px; border: 1px solid var(--line); border-radius: 8px; background: var(--card); cursor: pointer; }
    input[type="file"] { width: 100%; color: var(--text); font-size: 10px; }
    input[type="file"]::file-selector-button {
      margin-right:8px; padding:6px 9px; border:1px solid var(--line); border-radius:6px;
      color:var(--text); background:var(--surface); cursor:pointer;
    }
    .two-col, .three-col { display: grid; gap: 14px; }
    .two-col { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .three-col { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    .range-row { display: flex; justify-content: space-between; align-items: center; color: var(--muted); font-size: 10px; }
    .field-hint { margin: 5px 0 0; color: var(--muted); font-size: 9px; }
    .hidden { display: none !important; }
    .chip-list { display: flex; flex-wrap: wrap; gap: 6px; margin: 0 0 10px; }
    .skill-chip { display: inline-flex; align-items: center; gap: 5px; padding: 5px 8px; border-radius: 20px; background: var(--surface); font-size: 10px; }
    .remove-chip, .remove-item { border: 0; background: transparent; color: var(--muted); cursor: pointer; }
    .remove-chip { padding: 0; font-size: 14px; line-height: 1; }
    .remove-chip:hover, .remove-item:hover { color: var(--danger-hover); }
    .inline-add { display: grid; grid-template-columns: 1fr auto; gap: 7px; }
    .small-button { min-height: 35px; padding: 0 10px; border: 1px solid var(--line); border-radius: 8px; background: var(--card); cursor: pointer; font-size: 10px; font-weight: 700; }
    .small-button:hover { border-color: var(--accent); }
    .repeat-list { display: grid; gap: 9px; margin-bottom: 10px; }
    .repeat-card { padding: 11px; border: 1px solid var(--line); border-radius: 10px; background: var(--card); }
    .repeat-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 9px; color: var(--muted); font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; }
    .reorder-controls { display:flex; align-items:center; gap:5px; }
    .reorder-controls button { padding:2px 4px; border:0; color:var(--muted); background:transparent; cursor:pointer; font-size:10px; }
    .reorder-controls button:disabled { color:var(--muted); cursor:default; }
    .repeat-card .field { margin-bottom: 8px; }
    .repeat-card .field:last-child { margin-bottom: 0; }
    .repeat-card input, .repeat-card textarea { font-size: 10px; }
    .project-images { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:6px; margin-top:8px; }
    .project-images img { width:100%; aspect-ratio:1.3; object-fit:cover; border-radius:6px; }
    .item-images { display:flex; flex-wrap:wrap; gap:7px; margin-top:8px; }
    .existing-image { position:relative; width:48px; height:48px; }
    .existing-image img { width:100%; height:100%; object-fit:cover; border-radius:6px; }
    .existing-image button { position:absolute; top:-5px; right:-5px; width:18px; height:18px; padding:0; border:0; border-radius:50%; color:white; background:#a83e32; cursor:pointer; line-height:18px; }
    .check-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 9px; }
    .check-option { display: flex; align-items: center; gap: 7px; color: var(--muted); font-size: 10px; cursor: pointer; }
    .check-option input { accent-color: var(--accent); }
    .photo-controls { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; align-items: end; margin-top: 10px; }
    .preview-area {
      min-width: 0; padding: 30px clamp(18px, 4vw, 58px);
      background-color: var(--surface); overflow-y: auto;
      min-height:0; transition: var(--transition);
    }
    .preview-toolbar { max-width: 920px; margin: 0 auto 15px; display: flex; align-items: center; justify-content: space-between; }
    .preview-toolbar span { font-size: 10px; color: var(--muted); text-transform: uppercase; letter-spacing: .13em; font-weight: 700; }
    .preview-hint { color: var(--muted); font-size: 10px; }
    .portfolio {
      --portfolio-bg: #f8f7f2; --portfolio-ink: #23241f; --portfolio-muted: #686b63;
      --portfolio-card: #fff; --portfolio-accent: #5b6e58; --portfolio-radius: 14px;
      --portfolio-space: 1;
      position: relative; overflow: hidden; isolation: isolate;
      max-width: 920px; min-height: 710px; margin: 0 auto; padding: calc(48px * var(--portfolio-space));
      border-radius: var(--portfolio-radius); color: var(--portfolio-ink);
      background: var(--portfolio-bg); box-shadow: var(--shadow);
      font-size: var(--base-size, 16px); font-family: var(--portfolio-body, "DM Sans", sans-serif);
      transition: border-radius .3s ease, background-color .3s ease;
    }
    html[data-theme="light"] .generator-page .portfolio {
      outline: 1px solid #c8d0c8;
      outline-offset: 2px;
    }
    .portfolio::before {
      content: ""; position: absolute; inset: 0; z-index: -2;
      background-image: var(--portfolio-image, none); background-size: cover; background-position: center;
      opacity: var(--portfolio-image-opacity, 1);
    }
    .portfolio::after {
      content: ""; position: absolute; inset: 0; z-index: -1;
      background: var(--portfolio-overlay, transparent); pointer-events: none;
    }
    .portfolio-inner { position: relative; max-width: 720px; margin: auto; }
    .portfolio h1, .portfolio h2, .portfolio h3 { color: var(--portfolio-ink); font-family: var(--portfolio-heading, "DM Sans", sans-serif); }
    .portfolio a { color: var(--portfolio-accent); text-decoration-thickness: 1px; text-underline-offset: 3px; }
    .portfolio .eyeline { color: var(--portfolio-accent); font: 700 .75em "Roboto Mono", monospace; text-transform: uppercase; letter-spacing: .17em; }
    .portfolio-hero { display: flex; align-items: center; gap: 27px; margin-bottom: 39px; }
    .avatar {
      flex: 0 0 112px; width: 112px; height: 112px; overflow: hidden; background: color-mix(in srgb, var(--portfolio-accent) 15%, white);
      border-radius: 50%; display: grid; place-items: center;
    }
    .avatar.shape-rounded { border-radius: 22px; }
    .avatar.shape-square { border-radius: 0; }
    .avatar img { width: 100%; height: 100%; object-fit: cover; }
    .avatar-placeholder { color: var(--portfolio-accent); font: 600 2.2em var(--portfolio-heading, sans-serif); }
    .hero-copy { min-width: 0; flex: 1; }
    .hero-copy .eyeline { margin: 0 0 9px; }
    .portfolio-name { max-width: 14em; margin: 0; font-size: 2.2em; line-height: 1.04; letter-spacing: -.06em; overflow-wrap: anywhere; }
    .generator-page .portfolio .hero-copy h1.portfolio-name {
      max-width:14em; margin:0; font-size:2.2em; line-height:1.04; letter-spacing:-.06em; overflow-wrap:anywhere;
    }
    .portfolio-role { margin: 9px 0 0; color: var(--portfolio-muted); font-size: 1em; overflow-wrap: anywhere; }
    .portfolio-section { margin-top: calc(32px * var(--portfolio-space)); }
    .portfolio-section h2 { margin: 0 0 12px; font-size: 1.2em; letter-spacing: -.03em; }
    .section-kicker { display: flex; align-items: center; gap: 10px; }
    .section-kicker::after { content: ""; height: 1px; flex: 1; background: color-mix(in srgb, var(--portfolio-ink) 13%, transparent); }
    .portfolio-bio { max-width: 650px; color: var(--portfolio-muted); line-height: 1.75; white-space: pre-line; }
    .portfolio-skills { display: flex; flex-wrap: wrap; gap: 8px; }
    .portfolio-skill { padding: 7px 11px; border-radius: 30px; background: color-mix(in srgb, var(--portfolio-accent) 12%, var(--portfolio-card)); color: var(--portfolio-accent); font-size: 1em; }
    .project-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
    .project-card, .detail-card {
      padding: 17px; border-radius: var(--portfolio-radius); background: var(--portfolio-card);
      border: 1px solid color-mix(in srgb, var(--portfolio-ink) 8%, transparent);
    }
    .project-card h3, .detail-card h3 { margin: 0 0 7px; font-size: 1.2em; }
    .project-card p, .detail-card p { margin: 0; color: var(--portfolio-muted); line-height: 1.6; font-size: 1em; }
    .project-card a { display: inline-block; margin-top: 12px; font-size: 1em; font-weight: 700; }
    .detail-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
    .detail-card { padding: 13px; }
    .detail-card .detail-meta { margin-top: 5px; font-size: .75em; color: var(--portfolio-accent); }
    .contact-list { display: flex; flex-wrap: wrap; gap: 10px 18px; padding: 14px 0; border-top: 1px solid color-mix(in srgb, var(--portfolio-ink) 13%, transparent); }
    .contact-list a, .contact-list span { font-size: 1em; overflow-wrap: anywhere; }
    .template-card .portfolio-hero {
      padding:26px; border:1px solid color-mix(in srgb, var(--portfolio-accent) 24%, transparent);
      border-radius:var(--portfolio-radius); background:color-mix(in srgb, var(--portfolio-card) 88%, var(--portfolio-bg));
      box-shadow:0 14px 34px color-mix(in srgb, var(--portfolio-ink) 12%, transparent);
    }
    .template-card .portfolio-section {
      padding:20px; border:1px solid color-mix(in srgb, var(--portfolio-accent) 20%, transparent);
      border-radius:var(--portfolio-radius); background:color-mix(in srgb, var(--portfolio-card) 88%, var(--portfolio-bg));
      box-shadow:0 10px 26px color-mix(in srgb, var(--portfolio-ink) 8%, transparent);
    }
    .template-card .portfolio-section h2 { padding-bottom:10px; border-bottom:1px solid color-mix(in srgb, var(--portfolio-accent) 28%, transparent); }
    .template-card .project-card, .template-card .detail-card {
      border-color:color-mix(in srgb, var(--portfolio-accent) 24%, transparent);
      box-shadow:0 7px 20px color-mix(in srgb, var(--portfolio-ink) 10%, transparent);
    }
    .template-sidebar { padding: 0; }
    .template-sidebar .portfolio-inner { max-width: none; display: grid; grid-template-columns: minmax(180px, .72fr) 1.5fr; min-height: 710px; }
    .template-sidebar .portfolio-hero { display: block; margin: 0; padding: 42px 25px; background: color-mix(in srgb, var(--portfolio-accent) 9%, var(--portfolio-card)); }
    .template-sidebar .avatar { margin-bottom: 22px; }
    .template-sidebar .portfolio-name { font-size: 2.2em; }
    .template-sidebar .sidebar-content { padding: 42px 32px; }
    .template-sidebar .sidebar-content .portfolio-section:first-child { margin-top: 0; }
    .template-sidebar .contact-list { display: grid; gap: 9px; border: 0; }
    .empty-note { color: var(--portfolio-muted); font-style: italic; font-size: 1em; }
    .notice { min-height: 15px; margin: 7px 0 0; color: var(--muted); font-size: 10px; }
    .pattern-dots {
      background-image: radial-gradient(color-mix(in srgb, var(--portfolio-accent) 27%, transparent) 1px, transparent 1px);
      background-size: 16px 16px;
    }
    .pattern-grid {
      background-image: linear-gradient(color-mix(in srgb, var(--portfolio-accent) 14%, transparent) 1px, transparent 1px), linear-gradient(90deg, color-mix(in srgb, var(--portfolio-accent) 14%, transparent) 1px, transparent 1px);
      background-size: 24px 24px;
    }
    .pattern-stripes { background-image: repeating-linear-gradient(135deg, color-mix(in srgb, var(--portfolio-accent) 10%, transparent) 0 1px, transparent 1px 12px); }
    .pattern-waves {
      background-image: url("data:image/svg+xml,%3Csvg width='80' height='24' viewBox='0 0 80 24' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M0 12 Q10 2 20 12 T40 12 T60 12 T80 12' fill='none' stroke='%235b6e58' stroke-opacity='.22' stroke-width='1'/%3E%3C/svg%3E");
      background-size: 80px 24px;
    }
    @media (max-width: 900px) {
      .workspace { grid-template-columns: minmax(300px, 36%) minmax(0, 1fr); }
      .editor { padding-inline: 17px; }
      .preview-area { padding-inline: 20px; }
      .portfolio { padding: 36px 28px; }
    }
    @media (max-width: 700px) {
      .mobile-switch { display: flex; justify-self:end; gap: 3px; padding: 3px; border: 1px solid var(--line); border-radius: 9px; background: var(--surface); }
      .mobile-switch button { border: 0; background: transparent; color: var(--muted); padding: 6px 9px; border-radius: 6px; font-size: 10px; cursor: pointer; }
      .mobile-switch button[aria-pressed="true"] { color: var(--text); background: var(--card); box-shadow: 0 1px 5px rgb(0 0 0 / 10%); }
      .workspace { height:calc(100vh - 65px); display:grid; grid-template-columns:minmax(0, 1fr); grid-template-rows:auto minmax(0, 1fr); }
      .mobile-switch { grid-row:1; margin:8px 12px 4px; }
      .editor, .preview-area { grid-column:1; grid-row:2; min-height:0; overflow-y:auto; }
      .editor { padding: 13px 15px 32px; border-right: 0; }
      .preview-area { padding: 18px 12px 30px; }
      .workspace[data-mobile-view="edit"] .preview-area, .workspace[data-mobile-view="preview"] .editor { display: none; }
      .preview-toolbar { padding: 0 5px; }
      .portfolio { min-height: 680px; padding: 30px 21px; }
      .portfolio-hero { gap: 16px; margin-bottom: 29px; }
      .avatar { flex-basis: 76px; width: 76px; height: 76px; }
      .project-grid, .detail-grid { grid-template-columns: 1fr; }
      .template-sidebar .portfolio-inner { display: block; min-height: 0; }
      .template-sidebar .portfolio-hero { padding: 27px 22px; }
      .template-sidebar .avatar { margin-bottom: 14px; }
      .template-sidebar .sidebar-content { padding: 2px 22px 30px; }
    }
  </style>
<div class="generator-page" id="generator-page">
  <main class="workspace" id="workspace" data-mobile-view="edit">
    <div class="mobile-switch" role="group" aria-label="Workspace view">
      <button type="button" data-mobile-tab="edit" aria-pressed="true">Edit</button>
      <button type="button" data-mobile-tab="preview" aria-pressed="false">Preview</button>
    </div>
    <form id="editor-form" class="editor" aria-label="Portfolio customization" enctype="multipart/form-data" data-save-url="{{ $portfolio->exists ? route('portfolios.update', $portfolio) : route('portfolios.store') }}" data-method="{{ $portfolio->exists ? 'PUT' : 'POST' }}">
      @csrf
      @if($portfolio->exists) @method('PUT') @endif
      <div class="editor-heading">
        <h1>Create your folio</h1>
        <p class="subheading">Edit everything in one place. Your changes preview instantly and save to your account.</p>
      </div>
      <div class="editor-sections">
      <details class="editor-section accordion" open>
        <summary><div class="section-title"><h2>01 &nbsp; Overview</h2><span class="section-number">PORTFOLIO</span></div></summary>
        <div class="accordion-content">
          <label class="field"><span>Portfolio title</span><input type="text" data-portfolio-field="title" maxlength="120" required placeholder="My portfolio"></label>
          <label class="field"><span>Short description</span><textarea data-portfolio-field="description" maxlength="3000" required placeholder="What ties your work together?"></textarea></label>
          <div class="two-col">
            <label class="field"><span>Category</span><input type="text" data-portfolio-field="category" maxlength="80" placeholder="Design"></label>
            <label class="field"><span>Tags</span><input type="text" data-portfolio-field="tags" maxlength="500" placeholder="design, web, identity"></label>
          </div>
          <label class="field"><span>Portfolio cover image</span><input type="file" id="cover-upload" accept="image/*"></label>
          <div class="current-image" id="cover-current"></div>
        </div>
      </details>
      <details class="editor-section accordion" open>
        <summary><div class="section-title"><h2>02 &nbsp; About you</h2><span class="section-number">PROFILE</span></div></summary>
        <div class="accordion-content">
          <label class="field full-name-field"><span>Full name</span><input type="text" data-path="profile.name" autocomplete="name" placeholder="Your name"></label>
          <label class="field"><span>Job title or course</span><input type="text" data-path="profile.role" placeholder="BSIT Student"></label>
          <label class="field"><span>Short bio</span><textarea data-path="profile.bio" placeholder="A little about you and what you do."></textarea></label>
          <div class="two-col">
            <label class="field"><span>Email</span><input type="email" data-path="profile.contact.email" autocomplete="email"></label>
            <label class="field"><span>Phone</span><input type="tel" data-path="profile.contact.phone" autocomplete="tel"></label>
          </div>
          <label class="field"><span>Location</span><input type="text" data-path="profile.contact.location" autocomplete="address-level2"></label>
          <div class="two-col">
            <label class="field"><span>GitHub URL</span><input type="url" data-path="profile.contact.github" placeholder="https://github.com/you"></label>
            <label class="field"><span>LinkedIn URL</span><input type="url" data-path="profile.contact.linkedin" placeholder="https://linkedin.com/in/you"></label>
          </div>
        </div>
      </details>
      <details class="editor-section accordion">
        <summary><div class="section-title"><h2>03 &nbsp; Photo</h2><span class="section-number">IMAGE</span></div></summary>
        <div class="accordion-content">
          <label class="field"><span>Upload your photo</span><input type="file" id="photo-upload" accept="image/*"></label>
          <div class="current-image" id="photo-current"></div>
          <div class="photo-controls">
            <label class="field"><span>Photo shape</span><select data-path="profile.photo.shape"><option value="circle">Circle</option><option value="rounded">Rounded square</option><option value="square">Square</option></select></label>
            <label class="field"><span>Zoom</span><input type="range" min="1" max="2.5" step=".05" data-path="profile.photo.zoom"></label>
          </div>
          <div class="two-col">
            <label class="field"><span>Horizontal crop</span><input type="range" min="0" max="100" data-path="profile.photo.x"></label>
            <label class="field"><span>Vertical crop</span><input type="range" min="0" max="100" data-path="profile.photo.y"></label>
          </div>
        </div>
      </details>
      <details class="editor-section accordion">
        <summary><div class="section-title"><h2>04 &nbsp; Skills</h2><span class="section-number">EXPERTISE</span></div></summary>
        <div class="accordion-content">
          <div id="skills-editor" class="chip-list" aria-label="Skills"></div>
          <div class="inline-add" id="skill-form"><input id="skill-input" type="text" placeholder="e.g. UI Design" aria-label="Add a skill"><button class="small-button" id="skill-add" type="button">Add skill +</button></div>
        </div>
      </details>
      <details class="editor-section accordion" open>
        <summary><div class="section-title"><h2>05 &nbsp; Projects / items</h2><span class="section-number">SELECTED WORK</span></div></summary>
        <div class="accordion-content">
          <div class="repeat-list" id="items-editor"></div>
          <button class="small-button" type="button" data-add="items">Add a project +</button>
        </div>
      </details>
      <details class="editor-section accordion">
        <summary><div class="section-title"><h2>06 &nbsp; Education and experience</h2><span class="section-number">HISTORY</span></div></summary>
        <div class="accordion-content">
          <h3 class="subsection-heading">Education</h3><div class="repeat-list" id="education-editor"></div><button class="small-button" type="button" data-add="education">Add education +</button>
          <h3 class="subsection-heading">Experience</h3><div class="repeat-list" id="experience-editor"></div><button class="small-button" type="button" data-add="experience">Add experience +</button>
        </div>
      </details>
      <details class="editor-section accordion">
        <summary><div class="section-title"><h2>07 &nbsp; Appearance</h2><span class="section-number">LOOK & FEEL</span></div></summary>
        <div class="accordion-content">
          <div class="two-col">
            <label class="field"><span>Heading font</span><select data-path="appearance.style.headingFont"></select></label>
            <label class="field"><span>Body font</span><select data-path="appearance.style.bodyFont"></select></label>
          </div>
          <label class="field"><span class="range-row">Font size <output data-output="appearance.style.fontSize"></output></span><input type="range" min="12" max="24" step="1" data-path="appearance.style.fontSize"></label>
          <label class="field"><span>Accent color</span><input type="color" data-path="appearance.style.accent"></label>
          <label class="field"><span>Layout template</span><select data-path="appearance.style.template"><option value="minimal">Minimal</option><option value="card">Card-based</option><option value="sidebar">Sidebar</option></select></label>
          <div class="two-col">
            <label class="field"><span class="range-row">Corner roundness <output data-output="appearance.style.radius"></output></span><input type="range" min="0" max="32" data-path="appearance.style.radius"></label>
            <label class="field"><span class="range-row">Section spacing <output data-output="appearance.style.spacing"></output></span><input type="range" min=".7" max="1.5" step=".05" data-path="appearance.style.spacing"></label>
          </div>
          <label class="field"><span>Background design</span><select data-path="appearance.background.mode"><option value="solid">Solid color</option><option value="gradient">Gradient</option><option value="dots">Pattern: dots</option><option value="grid">Pattern: grid</option><option value="waves">Pattern: waves</option><option value="stripes">Pattern: diagonal stripes</option><option value="image">Upload background image</option></select></label>
          <div data-background-fields="solid"><label class="field"><span>Canvas color</span><input type="color" data-path="appearance.background.solid"></label></div>
          <div data-background-fields="gradient" class="hidden"><div class="two-col"><label class="field"><span>Color one</span><input type="color" data-path="appearance.background.color1"></label><label class="field"><span>Color two</span><input type="color" data-path="appearance.background.color2"></label></div><label class="field"><span class="range-row">Gradient angle <output data-output="appearance.background.angle"></output></span><input type="range" min="0" max="360" data-path="appearance.background.angle"></label></div>
          <div data-background-fields="image" class="hidden"><label class="field"><span>Upload background image</span><input type="file" id="background-upload" accept="image/*"></label><div class="current-image" id="background-current"></div><label class="field"><span class="range-row">Overlay opacity <output data-output="appearance.background.overlayOpacity"></output></span><input type="range" min="0" max=".9" step=".05" data-path="appearance.background.overlayOpacity"></label></div>
        </div>
      </details>
      <details class="editor-section accordion">
        <summary><div class="section-title"><h2>08 &nbsp; Sections</h2><span class="section-number">VISIBILITY</span></div></summary>
        <div class="accordion-content check-grid">
          <label class="check-option"><input type="checkbox" data-path="appearance.sections.about"> About</label>
          <label class="check-option"><input type="checkbox" data-path="appearance.sections.skills"> Skills</label>
          <label class="check-option"><input type="checkbox" data-path="appearance.sections.projects"> Projects</label>
          <label class="check-option"><input type="checkbox" data-path="appearance.sections.contact"> Contact</label>
        </div>
      </details>
      </div>
      <div class="editor-actions">
        <div class="generator-status" id="save-status" role="status" aria-live="polite"></div>
        <button class="small-button" id="reset-button" type="button">Reset to default</button>
        <p class="notice" id="editor-notice" role="status" aria-live="polite"></p>
        <button class="button button-primary save-button" id="save-button" type="submit">Save portfolio</button>
      </div>
    </form>
    <section class="preview-area" aria-label="Live portfolio preview">
      <div class="preview-toolbar"><span>Live preview</span><span class="preview-hint">Updates as you edit</span></div>
      <article class="portfolio template-minimal" id="portfolio-preview" aria-label="Portfolio preview"></article>
    </section>
  </main>
  <script type="application/json" id="editor-state">@json($editorState)</script>
  <script src="{{ asset('js/portfolio-editor.js') }}" defer></script>
@endsection