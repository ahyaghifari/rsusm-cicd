---
name: Compassionate Clinical Excellence
colors:
  surface: '#f8f9ff'
  surface-dim: '#d1dbec'
  surface-bright: '#f8f9ff'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#eef4ff'
  surface-container: '#e5eeff'
  surface-container-high: '#dfe9fa'
  surface-container-highest: '#d9e3f4'
  on-surface: '#121c28'
  on-surface-variant: '#52424d'
  inverse-surface: '#27313e'
  inverse-on-surface: '#eaf1ff'
  outline: '#84727e'
  outline-variant: '#d6c0ce'
  surface-tint: '#9b3191'
  primary: '#982e8e'
  on-primary: '#ffffff'
  primary-container: '#b649a9'
  on-primary-container: '#fffbff'
  inverse-primary: '#ffabee'
  secondary: '#006c4b'
  on-secondary: '#ffffff'
  secondary-container: '#96f6c8'
  on-secondary-container: '#00734f'
  tertiary: '#605a60'
  on-tertiary: '#ffffff'
  tertiary-container: '#797379'
  on-tertiary-container: '#fffbff'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#ffd7f3'
  primary-fixed-dim: '#ffabee'
  on-primary-fixed: '#390036'
  on-primary-fixed-variant: '#7e1277'
  secondary-fixed: '#96f6c8'
  secondary-fixed-dim: '#7ad9ad'
  on-secondary-fixed: '#002114'
  on-secondary-fixed-variant: '#005137'
  tertiary-fixed: '#e9e0e7'
  tertiary-fixed-dim: '#ccc4cb'
  on-tertiary-fixed: '#1e1a1f'
  on-tertiary-fixed-variant: '#4a454b'
  background: '#f8f9ff'
  on-background: '#121c28'
  surface-variant: '#d9e3f4'
typography:
  display:
    fontFamily: manrope
    fontSize: 48px
    fontWeight: '700'
    lineHeight: '1.1'
    letterSpacing: -0.02em
  display-mobile:
    fontFamily: manrope
    fontSize: 36px
    fontWeight: '700'
    lineHeight: '1.2'
    letterSpacing: -0.01em
  headline-lg:
    fontFamily: manrope
    fontSize: 32px
    fontWeight: '600'
    lineHeight: '1.3'
  headline-md:
    fontFamily: manrope
    fontSize: 24px
    fontWeight: '600'
    lineHeight: '1.4'
  body-lg:
    fontFamily: plusJakartaSans
    fontSize: 18px
    fontWeight: '400'
    lineHeight: '1.6'
  body-md:
    fontFamily: plusJakartaSans
    fontSize: 16px
    fontWeight: '400'
    lineHeight: '1.6'
  label-sm:
    fontFamily: plusJakartaSans
    fontSize: 14px
    fontWeight: '600'
    lineHeight: '1.4'
    letterSpacing: 0.05em
rounded:
  sm: 0.25rem
  DEFAULT: 0.5rem
  md: 0.75rem
  lg: 1rem
  xl: 1.5rem
  full: 9999px
spacing:
  base: 8px
  container-max: 1280px
  gutter: 24px
  section-padding-desktop: 120px
  section-padding-mobile: 64px
---

## Brand & Style

This design system is engineered for a leading healthcare provider, balancing institutional authority with approachable care. The brand personality is "Professional Warmth"—it must feel as technically proficient as a modern surgical center while remaining as comforting as a community clinic. 

The target audience spans all age demographics, from tech-savvy parents to elderly patients. The design style follows a **Corporate / Modern** aesthetic with a **Human-Centric** focus. It utilizes generous white space to reduce cognitive load and high-quality photography of smiling medical professionals and diverse patients to foster trust. The visual language avoids the coldness of traditional medical interfaces by using soft transitions, organic interactions, and a vibrant color palette.

## Colors

The palette is anchored by a vibrant Magenta (#c758b9) used for primary actions and energetic accents, ensuring the brand feels modern and warm. A deep Medical Green (#057a55) serves as the secondary color, grounding the design in health-industry standards and providing a sense of stability and growth. 

We utilize a Tertiary Lavender-White (#fdf4fb) for soft background sections to prevent the interface from feeling clinical or sterile. Neutrals are kept soft—avoiding pure blacks in favor of Slate Grays—to maintain an accessible, high-legibility experience for users with varying visual needs.

## Typography

The typography strategy focuses on clarity and contemporary style. **Manrope** is used for headlines; its geometric structure feels professional and modern, while its subtle roundness keeps it approachable. Large displays use tighter letter spacing for a premium "editorial" feel.

**Plus Jakarta Sans** is employed for all body copy and UI labels. Its open apertures and soft terminals make it exceptionally legible for healthcare information and forms. We maintain a strict vertical rhythm with a 1.6x line height for body text to ensure maximum readability for elderly patients or those in stressful situations.

## Layout & Spacing

The design system utilizes a **Fixed Grid** model for desktop, centered within a 1280px container to ensure content remains readable on wide monitors. The spacing rhythm is based on an 8px baseline grid, promoting mathematical harmony between elements.

- **Desktop (1024px+):** 12-column grid, 24px gutters, 120px vertical section spacing to allow the layout to "breathe."
- **Tablet (768px - 1023px):** 8-column grid, 24px gutters, 80px vertical section spacing. 
- **Mobile (Up to 767px):** 4-column grid, 16px gutters, 64px vertical section spacing. Use full-width components (e.g., stacked buttons) to accommodate touch targets.

## Elevation & Depth

To achieve the "High-quality CMS" aesthetic, the design system uses **Ambient Shadows** and **Tonal Layers**. Depth is used sparingly to signify interactivity and hierarchy rather than pure decoration.

- **Level 1 (Subtle):** Used for cards and input fields. A soft, diffused shadow (0px 4px 20px rgba(0,0,0,0.05)) to separate content from the background.
- **Level 2 (Hover):** Used when a user interacts with a card. The shadow deepens and the element lifts slightly (0px 12px 30px rgba(199, 88, 185, 0.1)), utilizing a subtle tint of the primary color.
- **Level 3 (Overlay):** Used for navigation menus and modals. High-diffusion shadows with a semi-transparent backdrop blur (12px) to keep the user focused on the active task.

## Shapes

The shape language is consistently **Rounded** (0.5rem base), reinforcing the "warm and accessible" brand pillar. Sharp corners are avoided to minimize the "clinical" feel. 

- **Standard Elements:** 8px (0.5rem) radius for buttons, input fields, and small cards.
- **Large Containers:** 16px (1rem) radius for feature cards and image containers.
- **Full Rounded:** Used specifically for "Appointment" buttons or "Status" chips to create a friendly, pill-shaped call to action.

## Components

- **Buttons:** Primary buttons use the Magenta background with white text. Secondary buttons use a Green outline. All buttons feature a 300ms transition on hover with a slight upward translate (2px).
- **Input Fields:** Large, accessible hit areas with 16px internal padding. Focus states should use a 2px Green border to signify a "safe" interaction.
- **Cards:** White backgrounds with the Level 1 shadow. Healthcare services should be categorized with small Magenta icons.
- **Doctor Profiles:** Use circular or 1rem rounded-rect images with a subtle 4px border in Tertiary Magenta to highlight the "human" aspect of the hospital.
- **Appointment Widget:** A high-contrast component, potentially using the Green secondary color as a background to stand out as the most critical functional element of the landing page.
- **Checkboxes & Radios:** Should be slightly larger than standard (20px) to ensure accessibility for older patients.