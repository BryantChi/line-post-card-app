/**
 * 递归渲染任意符合 LINE Flex Message 规范的 JSON，生成对应的 DOM 元素
 * 增强版：更完整地支持官方规范，包括更多属性、默认值处理和样式细节。
 */

// --- 全域顏色處理相關常量與輔助函式 ---
const DEFAULT_TEXT_COLOR = "#111111";
const DEFAULT_VISIBLE_OFF_WHITE_FOR_WHITE_TEXT = "#CCCCCC";
const DEFAULT_BOX_BACKGROUND_COLOR = "transparent";
const DEFAULT_SEPARATOR_COLOR = "#e0e0e0";
const DEFAULT_IMAGE_WRAPPER_BACKGROUND_COLOR = "transparent";

function isPlaceholderColor(colorString) {
    // 判斷是否為佔位符顏色 "{{}}"
  return typeof colorString === 'string' && colorString.match(/^\{\{.*\}\}$/);
}

function isEffectivelyWhite(colorStr) {
  if (!colorStr || typeof colorStr !== 'string') return false;
  const lowerColorStr = colorStr.toLowerCase().trim();
  if (['white', '#fff', '#ffffff'].includes(lowerColorStr)) {
    return true;
  }
  if (lowerColorStr.startsWith('rgb')) { // Handles rgb() and rgba()
    try {
      const parts = lowerColorStr.match(/\d+/g);
      if (parts && parts.length >= 3) {
        return parseInt(parts[0]) === 255 && parseInt(parts[1]) === 255 && parseInt(parts[2]) === 255;
      }
    } catch (e) { /* ignore parse error */ }
  }
  return false;
}

function resolveColor(colorString, defaultColor) {
  if (colorString === null || colorString === undefined) return defaultColor;
  if (typeof colorString === 'string' && colorString.trim() === "") return null; // Empty string means inherit or browser default
  if (isPlaceholderColor(colorString)) return defaultColor;
  if (isValidColor(colorString)) return colorString;
  return defaultColor; // Fallback for other invalid formats
}
// --- 顏色處理輔助函式結束 ---

function renderFlexComponent(component, role = "", parentBubbleStyles = {}) {
  if (!component || typeof component !== "object" || !component.type) return null;

  let el = null;
  const type = component.type;

  // Helper function to apply common styles
  function applyCommonStyles(element, comp) {
    if (!element || !comp) return;

    // flex
    if (comp.flex !== undefined) {
      // LINE's flex: 0 means "0 0 auto" (no grow, no shrink, basis is content size)
      // LINE's flex: N (N > 0) means "N 1 0%" (grow N, shrink 1, basis is 0)
      if (Number(comp.flex) === 0) {
        element.style.flex = "0 0 auto";
      } else if (Number(comp.flex) > 0) {
        element.style.flex = `${comp.flex} 1 0%`; // Explicitly set grow, shrink, basis
        // Add a class to handle min-width/min-height in CSS based on parent orientation
        // This helps items shrink below their intrinsic content size if necessary
        element.classList.add("flex-item-can-shrink");
      }
      // If comp.flex is < 0, it's invalid by LINE spec, current behavior is to not apply if not 0 or >0.
      // This could be made stricter if needed.
    }

    // margin (keyword or specific value)
    if (comp.margin) {
      if (["none", "xs", "sm", "md", "lg", "xl", "xxl"].includes(comp.margin.toLowerCase())) {
        element.classList.add(`margin-${comp.margin.toLowerCase()}`);
      } else {
        element.style.marginTop = comp.margin; // LINE Flex margin is only margin-top
      }
    }

    // width & height (keyword for predefined classes or specific value)
    if (comp.width) element.style.width = comp.width;
    if (comp.height) {
      if (["xxs", "xs", "sm", "md", "lg", "xl", "xxl"].includes(comp.height.toLowerCase())) {
        element.classList.add(`height-${comp.height.toLowerCase()}`);
      } else {
        element.style.height = comp.height;
      }
    }

    // position & offsets
    if (comp.position === "absolute") {
      element.classList.add("position-absolute");
      element.style.display = "block";
      if (comp.offsetTop) element.style.top = comp.offsetTop;
      if (comp.offsetBottom) element.style.bottom = comp.offsetBottom;
      if (comp.offsetStart) element.style.left = comp.offsetStart;
      if (comp.offsetEnd) element.style.right = comp.offsetEnd;
    } else if (comp.position === "relative") {
      element.classList.add("position-relative");
      element.style.display = "block";
      // Relative positioning with offsets
      if (comp.offsetTop) element.style.top = comp.offsetTop;
      if (comp.offsetBottom) element.style.bottom = comp.offsetBottom;
      if (comp.offsetStart) element.style.left = comp.offsetStart;
      if (comp.offsetEnd) element.style.right = comp.offsetEnd;
    } else { // No explicit position, but offsets are present
      if (comp.offsetTop || comp.offsetBottom || comp.offsetStart || comp.offsetEnd) {
        element.style.position = "relative"; // Make offsets work
        if (comp.offsetTop) element.style.top = comp.offsetTop;
        if (comp.offsetBottom) element.style.bottom = comp.offsetBottom;
        if (comp.offsetStart) element.style.left = comp.offsetStart;
        if (comp.offsetEnd) element.style.right = comp.offsetEnd;
      }
    }

    // Action
    if (comp.action) {
      element.style.cursor = "pointer";
      element.addEventListener("click", (e) => {
        e.stopPropagation(); // Prevent event bubbling if nested actions
        handleAction(comp.action);
      });
    }
  }

  function handleAction(action) {
    if (!action || !action.type) return;
    switch (action.type) {
      case "uri":
        if (action.uri) window.open(action.uri, "_blank");
        break;
      case "postback":
        console.log("Postback action:", { label: action.label, data: action.data, text: action.displayText });
        break;
      case "message":
        console.log("Message action:", { label: action.label, text: action.text });
        break;
      // Add other action types like datetimepicker, camera, cameraRoll, location, richmenu
      default:
        console.log("Unhandled action type:", action.type);
    }
  }


  switch (type) {
    case "carousel": {
      el = document.createElement("div");
      el.classList.add("carousel-container");
      if (component.direction) {
        el.setAttribute("direction", component.direction);
      }
      if (Array.isArray(component.contents)) {
        component.contents.forEach((bubbleJson) => {
          const bubbleEl = renderFlexComponent(
            { ...bubbleJson, direction: component.direction }, // Pass direction to bubble
            "bubble"
          );
          if (bubbleEl) el.appendChild(bubbleEl);
        });
      }
      break;
    }

    case "bubble": {
      el = document.createElement("div");
      el.classList.add("bubble-container");
      if (component.size) el.setAttribute("size", component.size);
      if (component.direction) el.setAttribute("direction", component.direction);

      const bubbleStyles = component.styles || {};
      const defaultBubbleBg = "transparent"; // Or specific default if needed

      if (component.header) {
        const headerEl = renderFlexComponent(component.header, "header", bubbleStyles);
        if (headerEl) {
          let headerBg = bubbleStyles.header && bubbleStyles.header.backgroundColor;
          headerEl.style.backgroundColor = resolveColor(headerBg, defaultBubbleBg);
          el.appendChild(headerEl);
        }
      }
      if (component.hero) {
        const heroEl = renderFlexComponent(component.hero, "hero", bubbleStyles);
        if (heroEl) {
           let heroBg = bubbleStyles.hero && bubbleStyles.hero.backgroundColor;
           // Hero itself might be an image, bg is for wrapper if any, or if hero is a box
           heroEl.style.backgroundColor = resolveColor(heroBg, defaultBubbleBg);
           el.appendChild(heroEl);
        }
      }
      if (component.body) {
        const bodyEl = renderFlexComponent(component.body, "body", bubbleStyles);
        if (bodyEl) {
          let bodyBg = bubbleStyles.body && bubbleStyles.body.backgroundColor;
          bodyEl.style.backgroundColor = resolveColor(bodyBg, defaultBubbleBg);
          el.appendChild(bodyEl);
        }
      }
      if (component.footer) {
        const footerEl = renderFlexComponent(component.footer, "footer", bubbleStyles);
        if (footerEl) {
          let footerBg = bubbleStyles.footer && bubbleStyles.footer.backgroundColor;
          footerEl.style.backgroundColor = resolveColor(footerBg, defaultBubbleBg);
          el.appendChild(footerEl);
        }
      }
      // Apply bubble's own action if any (less common for whole bubble, usually on components)
      applyCommonStyles(el, component);
      break;
    }

    case "box": {
      el = document.createElement("div");
      el.classList.add("flex-box");

      // Layout
      if (component.layout === "vertical") el.classList.add("flex-vertical");
      else if (component.layout === "horizontal") el.classList.add("flex-horizontal");
      else if (component.layout === "baseline") el.classList.add("flex-baseline");
      else el.classList.add("flex-vertical"); // Default layout

      // Spacing (gap between children)
      if (component.spacing && ["none", "xs", "sm", "md", "lg", "xl", "xxl"].includes(component.spacing.toLowerCase())) {
        el.classList.add(`spacing-${component.spacing.toLowerCase()}`);
      } else if (component.spacing) {
        el.style.gap = component.spacing;
      }

      // Padding
      if (component.paddingAll) {
        if (["none", "xs", "sm", "md", "lg", "xl", "xxl"].includes(component.paddingAll.toLowerCase())) {
            el.classList.add(`padding-all-${component.paddingAll.toLowerCase()}`);
        } else {
            el.style.padding = component.paddingAll;
        }
      }
      if (component.paddingTop) el.style.paddingTop = component.paddingTop;
      if (component.paddingBottom) el.style.paddingBottom = component.paddingBottom;
      if (component.paddingStart) el.style.paddingLeft = component.paddingStart;
      if (component.paddingEnd) el.style.paddingRight = component.paddingEnd;

      // 若無任意 margin/padding，預設添加一個 class
      if (!component.margin && !component.paddingAll && !component.paddingTop && !component.paddingBottom && !component.paddingStart && !component.paddingEnd) {
        el.classList.add("default-padding");
      }

      // Background Color
      let boxBgColor = resolveColor(component.backgroundColor, DEFAULT_BOX_BACKGROUND_COLOR);
      if (boxBgColor) { // Only set if not null (i.e., not an empty string meant for inheritance)
          el.style.backgroundColor = boxBgColor;
      }
      // Border
      if (component.borderWidth) {
        el.style.borderWidth = component.borderWidth;
        el.style.borderStyle = "solid"; // Default style
        if (component.borderColor && isValidColor(component.borderColor)) {
          el.style.borderColor = component.borderColor;
        } else {
          el.style.borderColor = "#000000"; // Default border color if width is set but no color
        }
      } else if (component.borderColor && isValidColor(component.borderColor)) {
        el.style.borderWidth = "1px"; // Default width if only color is set
        el.style.borderStyle = "solid";
        el.style.borderColor = component.borderColor;
      }
      if (component.cornerRadius) {
        const cr = component.cornerRadius.toLowerCase();
        if (["none", "xs", "sm", "md", "lg", "xl"].includes(cr)) { // Common keywords
          el.classList.add(`corner-radius-${cr}`);
        } else {
          el.style.borderRadius = component.cornerRadius; // Direct CSS value
        }
      }

      // Flex item alignment
      if (component.justifyContent) el.style.justifyContent = component.justifyContent;
      if (component.alignItems) el.style.alignItems = component.alignItems;

      // Apply common styles (flex, margin, width, height, position, offsets, action)
      applyCommonStyles(el, component);

      // Role-specific classes
      if (role === "header") el.classList.add("bubble-header");
      if (role === "body") el.classList.add("bubble-body");
      if (role === "footer") el.classList.add("bubble-footer");

      // Contents
      if (Array.isArray(component.contents)) {
        component.contents.forEach((child) => {
          const childEl = renderFlexComponent(child, child.type, parentBubbleStyles); // Pass bubbleStyles for context if needed
          if (childEl) el.appendChild(childEl);
        });
      }
      break;
    }

    case "text": {
      el = document.createElement("div"); // Use div for better styling control with maxLines etc.
      el.classList.add("text-content");
      el.textContent = component.text || "";

      // Size (keyword or specific px value)
      if (component.size) {
        const s = component.size.toLowerCase();
        if (["xxxs", "xxs", "xs", "sm", "md", "lg", "xl", "xxl", "3xl", "4xl", "5xl"].includes(s)) {
          el.classList.add(`text-size-${s}`);
        } else {
          el.style.fontSize = component.size;
        }
      }

      // Weight
      if (component.weight === "bold") el.classList.add("text-bold");
      else el.classList.add("text-regular"); // Default

      // Color
      let textColor = resolveColor(component.color, DEFAULT_TEXT_COLOR);
      if (textColor && isEffectivelyWhite(textColor)) {
        textColor = DEFAULT_VISIBLE_OFF_WHITE_FOR_WHITE_TEXT; // 更新：使用新的灰白色
      }

      if (textColor) { // Only set if not null
          el.style.color = textColor;
      }


      // Align
      if (component.align) {
        if (["start", "end", "center"].includes(component.align)) {
          el.classList.add(`align-${component.align}`);
        }
      }

      // Gravity (vertical alignment in parent flex container)
      if (component.gravity) {
        if (component.gravity === "top") el.style.alignSelf = "flex-start";
        else if (component.gravity === "center") el.style.alignSelf = "center";
        else if (component.gravity === "bottom") el.style.alignSelf = "flex-end";
      }

      // Wrap
      if (component.wrap === true) el.classList.add("wrap");
      else if (component.wrap === false) el.classList.add("nowrap");
      // Default: normal wrap

      // MaxLines
      if (component.maxLines !== undefined && component.maxLines > 0) {
        el.classList.add(`max-lines-${component.maxLines}`);
      }

      // Decoration
      if (component.decoration === "underline") el.classList.add("underline");
      else if (component.decoration === "line-through") el.classList.add("line-through");

      // Style (italic)
      if (component.style === "italic") el.classList.add("font-style-italic");

      // LineSpacing
      if (component.lineSpacing) el.style.lineHeight = component.lineSpacing; // Or adjust based on font size

      // AdjustMode (basic handling)
      if (component.adjustMode === "shrink-to-fit") {
        // This is complex. For now, ensure it doesn't overflow if maxLines is set.
        // True shrink-to-fit (font scaling) is hard with pure CSS for dynamic content.
      }

      applyCommonStyles(el, component);
      break;
    }

    case "image": {
      const wrapper = document.createElement("div"); // Wrapper for aspect ratio and other styles
      wrapper.classList.add("image-container"); // General class for image wrapper

      const img = document.createElement("img");
      const defaultImageUrl = "/assets/admin/img/chenibg01.jpg"; // Placeholder
      img.src = component.url && isValidUrl(component.url) ? component.url : defaultImageUrl;
      img.alt = component.altText || component.alt || ""; // altText is official

      // AspectRatio & AspectMode
      if (component.aspectRatio) {
        wrapper.style.position = "relative";
        const ratioParts = component.aspectRatio.split(":").map(Number);
        if (ratioParts.length === 2 && ratioParts[0] > 0 && ratioParts[1] > 0) {
          const paddingTop = (ratioParts[1] / ratioParts[0]) * 100;
          wrapper.style.paddingTop = `${paddingTop}%`; // Use padding-top for aspect ratio
          img.style.position = "absolute";
          img.style.top = "0";
          img.style.left = "0";
          img.style.width = "100%";
          img.style.height = "100%";
        }
      } else {
         // If no aspectRatio, image will take its natural size or be affected by size prop
      }

      if (component.aspectMode === "cover") {
        img.style.objectFit = "cover";
      } else if (component.aspectMode === "fit") {
        img.style.objectFit = "contain";
      } else {
        img.style.objectFit = "cover"; // Default
      }

      // Size (keyword or specific value)
      if (component.size) {
        const s = component.size.toLowerCase();
        if (["xxs", "xs", "sm", "md", "lg", "xl", "xxl", "3xl", "4xl", "5xl", "fullwidth"].includes(s)) {
          wrapper.classList.add(`image-size-${s}`); // CSS handles fixed sizes or fullwidth
          if (s === "fullwidth") {
            wrapper.style.width = "100%";
            if (!component.aspectRatio) img.style.width = "100%"; // Image itself also 100% if no AR
          }
        } else { // Specific CSS size like "100px"
          wrapper.style.width = component.size;
          wrapper.style.height = component.size; // Assuming square if one value, or let CSS handle
          img.style.width = "100%";
          img.style.height = "100%";
        }
      }


      // Gravity (object-position for cover/contain)
      if (component.gravity) {
        if (component.gravity === "top") img.style.objectPosition = "top";
        else if (component.gravity === "center") img.style.objectPosition = "center";
        else if (component.gravity === "bottom") img.style.objectPosition = "bottom";
        // Could also support left/right combinations if needed
      }

      // Align (align-self for the wrapper in a flex container)
      if (component.align) {
        if (component.align === "start") wrapper.style.alignSelf = "flex-start";
        else if (component.align === "center") wrapper.style.alignSelf = "center";
        else if (component.align === "end") wrapper.style.alignSelf = "flex-end";
      }

      // Background color for the wrapper (e.g., if image is smaller or transparent)
      let imgWrapperBg = resolveColor(component.backgroundColor, DEFAULT_IMAGE_WRAPPER_BACKGROUND_COLOR);
      if (imgWrapperBg) {
          wrapper.style.backgroundColor = imgWrapperBg;
      }

      // Animated (boolean) - currently no special rendering for animated=true vs false
      // if (component.animated) { /* Potentially add a class or attribute */ }

      wrapper.appendChild(img);
      applyCommonStyles(wrapper, component); // Apply to wrapper
      el = wrapper;
      break;
    }

    case "icon": {
      el = document.createElement("img"); // Icon is just an img
      el.classList.add("icon-content");
      const defaultIconUrl = "../assets/admin/img/ci.png"; // Placeholder
      el.src = component.url && isValidUrl(component.url) ? component.url : defaultIconUrl;

      // Size (keyword or specific px)
      if (component.size) {
        const s = component.size.toLowerCase();
        if (["xxs", "xs", "sm", "md", "lg", "xl", "xxl", "3xl", "4xl", "5xl"].includes(s)) {
          el.classList.add(`icon-size-${s}`);
        } else {
          el.style.width = component.size;
          el.style.height = component.size; // Icons are usually square
        }
      } else {
        el.classList.add("icon-size-md"); // Default size
      }

      // AspectRatio (usually 1:1 for icons, but can be specified)
      if (component.aspectRatio) {
        // If icon is an img, aspectRatio is intrinsic or controlled by width/height.
        // If it were a div with background-image, this would be more relevant.
      }
      applyCommonStyles(el, component);
      break;
    }

    case "button": {
      el = document.createElement("button");
      el.classList.add("button-content");
      const action = component.action || {};
      el.textContent = action.label || "";

      // --- 重構按鈕顏色邏輯 ---
      const buttonStyle = component.style || "primary"; // Default to primary if not specified

      if (buttonStyle === "link") {
        el.classList.add("btn-link");
        const defaultLinkColor = "#1B74E4"; // Default for btn-link style
        let resolvedColor = resolveColor(component.color, defaultLinkColor);

        if (resolvedColor === null && component.color === "") {
            // Explicitly empty string, let CSS handle
        } else if (resolvedColor) {
            el.style.color = isEffectivelyWhite(resolvedColor) ? DEFAULT_VISIBLE_OFF_WHITE_FOR_WHITE_TEXT : resolvedColor;
        } else { // Fallback if component.color was undefined and resolveColor returned its default
            el.style.color = isEffectivelyWhite(defaultLinkColor) ? DEFAULT_VISIBLE_OFF_WHITE_FOR_WHITE_TEXT : defaultLinkColor;
        }

      } else if (buttonStyle === "primary") {
        el.classList.add("btn-primary");
        const defaultPrimaryBg = "#1B74E4"; // Default for btn-primary background
        let resolvedBgColor = resolveColor(component.color, defaultPrimaryBg);

        if (resolvedBgColor === null && component.color === "") {
            // Explicitly empty string for background, let CSS handle
        } else if (resolvedBgColor) {
            el.style.backgroundColor = resolvedBgColor;
        } else {
             el.style.backgroundColor = defaultPrimaryBg; // Ensure default if component.color was undefined
        }

        // Text color for primary is usually white by CSS. Adjust if needed.
        // Must be done after classes and potential inline styles are set to get the correct computed style.
        window.setTimeout(() => { // Use setTimeout to ensure styles are applied
            let currentTextColor = window.getComputedStyle(el).color;
            if (isEffectivelyWhite(currentTextColor)) {
                el.style.color = DEFAULT_VISIBLE_OFF_WHITE_FOR_WHITE_TEXT;
            }
        }, 0);


      } else if (buttonStyle === "secondary") {
        el.classList.add("btn-secondary");
        const defaultSecondaryColor = "#555555"; // Default for btn-secondary text & border
        let resolvedColor = resolveColor(component.color, defaultSecondaryColor);

        if (resolvedColor === null && component.color === "") {
            // Explicitly empty string, let CSS handle for border and text
        } else if (resolvedColor) {
            el.style.borderColor = resolvedColor;
            el.style.color = isEffectivelyWhite(resolvedColor) ? DEFAULT_VISIBLE_OFF_WHITE_FOR_WHITE_TEXT : resolvedColor;
        } else { // Fallback if component.color was undefined
            el.style.borderColor = defaultSecondaryColor;
            el.style.color = isEffectivelyWhite(defaultSecondaryColor) ? DEFAULT_VISIBLE_OFF_WHITE_FOR_WHITE_TEXT : defaultSecondaryColor;
        }
      }
      // --- 按鈕顏色邏輯結束 ---

      // Height (keyword)
      if (component.height === "sm") el.classList.add("btn-sm");
      else if (component.height === "md") el.classList.add("btn-md");
      // Default height from general button CSS

      // Gravity (vertical alignment)
      if (component.gravity) {
        if (component.gravity === "top") el.style.alignSelf = "flex-start";
        else if (component.gravity === "center") el.style.alignSelf = "center";
        else if (component.gravity === "bottom") el.style.alignSelf = "flex-end";
      }

      // AdjustMode (shrink-to-fit is default for buttons if no width constraint)
      // if (component.adjustMode === "shrink-to-fit") { /* usually default */ }

      applyCommonStyles(el, component); // Handles action, flex, margin, position, etc.
      break;
    }

    case "separator": {
      el = document.createElement("div");
      el.classList.add("separator-content"); // Matched to styles.css
      let sepColor = resolveColor(component.color, DEFAULT_SEPARATOR_COLOR);
      if (sepColor) {
        el.style.backgroundColor = sepColor;
      }
      // Margin is handled by applyCommonStyles if needed (though less common for separator to have its own margin class)
      if (component.margin) { // Separator margin is usually spacing from other elements
         if (["none", "xs", "sm", "md", "lg", "xl", "xxl"].includes(component.margin.toLowerCase())) {
            el.classList.add(`margin-${component.margin.toLowerCase()}`);
         } else {
            el.style.marginTop = component.margin;
         }
      }
      // Separator doesn't usually have flex, position, etc.
      break;
    }

    case "spacer": {
      el = document.createElement("div");
      el.classList.add("spacer-content");
      if (component.size) {
        if (["xs", "sm", "md", "lg", "xl", "xxl"].includes(component.size.toLowerCase())) {
          el.classList.add(`spacer-${component.size.toLowerCase()}`);
        } else {
          el.style.height = component.size; // Allow specific px for spacer height
        }
      } else {
        el.classList.add("spacer-md"); // Default size
      }
      // Spacers can have flex, margin, etc.
      applyCommonStyles(el, component);
      break;
    }

    case "filler": {
      el = document.createElement("div");
      el.classList.add("filler-content"); // Matched to styles.css
      // Ensure filler always has flex: 1 by default if not specified
      if (component.flex === undefined) {
        component.flex = 1; // Default flex for filler
      }
      applyCommonStyles(el, component); // Filler can also have flex property overridden
      break;
    }

    // Placeholder for other types like video, etc.
    // case "video": { ... break; }

    default:
      console.warn("Unsupported component type:", type);
      return null;
  }

  return el;
}

// 檢查 URL 是否有效
function isValidUrl(string) {
  try {
    new URL(string);
    return true;
  } catch (_) {
    return false;
  }
}

function isValidColor(color) {
  if (typeof color !== 'string' || !color.trim() || isPlaceholderColor(color)) { // Added placeholder check here
    return false;
  }
  const s = new Option().style;
  s.color = color;
  // Check if the browser could parse it and it's not an empty string if 'color' was empty.
  return s.color !== '' && (color.toLowerCase().trim() === 'transparent' ? s.color === 'transparent' : true);
}




/**
 * 页面加载后，将 flexJson 渲染到 #flex-root
 */
document.addEventListener("DOMContentLoaded", () => {
  // 使用你最新提供的 JSON 示例，确保 footer 正常显示
//   const flexJson = {
//     type: "carousel",
//     direction: "ltr", // 可选 ltr/rtl
//     contents: [
//       {
//         type: "bubble",
//         body: {
//           type: "box",
//           layout: "vertical",
//           contents: [
//             {
//               type: "image",
//               url: "https://business.cheni.tw/uploads/images/card_bubbles/1749520425_01.jpg",
//               size: "full",
//               aspectMode: "cover",
//               gravity: "top",
//               aspectRatio: "2:3",
//             },
//             {
//               type: "box",
//               layout: "vertical",
//               contents: [
//                 {
//                   type: "text",
//                   text: "誠翊資訊 負責人 李俊彥",
//                   size: "lg",
//                   weight: "bold",
//                   color: "#2B2177",
//                 },
//                 {
//                   type: "box",
//                   layout: "horizontal",
//                   contents: [
//                     {
//                       type: "box",
//                       layout: "vertical",
//                       contents: [
//                         {
//                           type: "text",
//                           text: "打電話",
//                           color: "#ffffff",
//                           align: "center",
//                         },
//                       ],
//                       backgroundColor: "#414297",
//                       cornerRadius: "md",
//                       paddingTop: "8px",
//                       paddingBottom: "8px",
//                       action: {
//                         type: "uri",
//                         label: "action",
//                         uri: "tel:0933488012",
//                       },
//                     },
//                     {
//                       type: "box",
//                       layout: "vertical",
//                       contents: [
//                         {
//                           type: "text",
//                           text: "+LINE",
//                           color: "#ffffff",
//                           align: "center",
//                         },
//                       ],
//                       width: "100px",
//                       backgroundColor: "#6C4E96",
//                       cornerRadius: "md",
//                       paddingTop: "8px",
//                       paddingBottom: "8px",
//                       action: {
//                         type: "uri",
//                         label: "action",
//                         uri: "https://line.me/ti/p/02r3T6OZJ_",
//                       },
//                     },
//                   ],
//                   offsetTop: "10px",
//                   spacing: "md",
//                 },
//               ],
//               paddingAll: "10px",
//               paddingBottom: "20px",
//             },
//           ],
//           paddingAll: "0px",
//         },
//       },
//       {
//         type: "bubble",
//         hero: {
//           type: "image",
//           url: "https://business.cheni.tw/uploads/images/card_bubbles/1749520719_02.jpg",
//           size: "full",
//           aspectRatio: "20:15",
//           aspectMode: "cover",
//           action: {
//             type: "uri",
//             uri: "https://line.me/",
//           },
//         },
//         body: {
//           type: "box",
//           layout: "vertical",
//           contents: [
//             {
//               type: "text",
//               text: "WEB I 網頁設計規劃",
//               size: "xl",
//               weight: "bold",
//               color: "#2B2177",
//             },
//             {
//               type: "box",
//               layout: "vertical",
//               margin: "md",
//               contents: [
//                 {
//                   type: "text",
//                   text: "客製化響應式設計、程式、視覺完美搭配",
//                   size: "sm",
//                   color: "#575770",
//                   margin: "md",
//                   flex: 0,
//                 },
//                 {
//                   type: "text",
//                   text: "企業公司形象功能網站",
//                   size: "sm",
//                   color: "#575770",
//                   margin: "md",
//                   flex: 0,
//                 },
//                 {
//                   type: "text",
//                   text: "網站關鍵字優化 | 排名第一頁產生效益",
//                   size: "sm",
//                   color: "#575770",
//                   margin: "md",
//                   flex: 0,
//                 },
//               ],
//             },
//           ],
//         },
//         footer: {
//           type: "box",
//           layout: "vertical",
//           spacing: "sm",
//           contents: [
//             {
//               type: "box",
//               layout: "vertical",
//               contents: [
//                 {
//                   type: "text",
//                   text: "觀看精質案例",
//                   color: "#ffffff",
//                   align: "center",
//                 },
//               ],
//               backgroundColor: "#8F98DE",
//               cornerRadius: "md",
//               paddingTop: "8px",
//               paddingBottom: "8px",
//               action: {
//                 type: "uri",
//                 label: "action",
//                 uri: "https://cheni.com.tw/case.php",
//               },
//             },
//             {
//               type: "box",
//               layout: "vertical",
//               contents: [
//                 {
//                   type: "text",
//                   text: "電話諮詢",
//                   color: "#ffffff",
//                   align: "center",
//                 },
//               ],
//               backgroundColor: "#8F98DE",
//               cornerRadius: "md",
//               paddingTop: "8px",
//               paddingBottom: "8px",
//               action: {
//                 type: "uri",
//                 label: "action",
//                 uri: "tel:038511126",
//               },
//             },
//             {
//               type: "box",
//               layout: "vertical",
//               contents: [
//                 {
//                   type: "text",
//                   text: "分享名片給朋友",
//                   color: "#ffffff",
//                   align: "center",
//                 },
//               ],
//               backgroundColor: "#5E5DB0",
//               cornerRadius: "md",
//               paddingTop: "8px",
//               paddingBottom: "8px",
//               action: {
//                 type: "uri",
//                 label: "分享名片給朋友",
//                 uri: "https://business.cheni.tw/share/4b4553dc-9186-4908-84e0-f5f2a79e40c4",
//               },
//             },
//             {
//               type: "box",
//               layout: "vertical",
//               contents: [],
//               margin: "sm",
//             },
//           ],
//           flex: 0,
//         },
//       },
//       {
//         type: "bubble",
//         hero: {
//           type: "image",
//           url: "https://business.cheni.tw/uploads/images/card_bubbles/1749521447_03.jpg",
//           size: "full",
//           aspectRatio: "20:15",
//           aspectMode: "cover",
//           action: {
//             type: "uri",
//             uri: "https://line.me/",
//           },
//         },
//         body: {
//           type: "box",
//           layout: "vertical",
//           contents: [
//             {
//               type: "text",
//               text: "MARKETING I 網路行銷",
//               size: "xl",
//               weight: "bold",
//               color: "#2B2177",
//             },
//             {
//               type: "box",
//               layout: "vertical",
//               margin: "md",
//               contents: [
//                 {
//                   type: "text",
//                   text: "Google商家優化 / 關鍵字廣告投放",
//                   size: "sm",
//                   color: "#575770",
//                   margin: "md",
//                   flex: 0,
//                 },
//                 {
//                   type: "text",
//                   text: "網站SEO優化 / Facebook粉絲團經營",
//                   size: "sm",
//                   color: "#575770",
//                   margin: "md",
//                   flex: 0,
//                 },
//                 {
//                   type: "text",
//                   text: "LINE官方帳號內容產製",
//                   size: "sm",
//                   color: "#575770",
//                   margin: "md",
//                   flex: 0,
//                 },
//               ],
//             },
//           ],
//         },
//         footer: {
//           type: "box",
//           layout: "vertical",
//           spacing: "sm",
//           contents: [
//             {
//               type: "box",
//               layout: "vertical",
//               contents: [
//                 {
//                   type: "text",
//                   text: "行銷方案介紹",
//                   color: "#ffffff",
//                   align: "center",
//                 },
//               ],
//               backgroundColor: "#8F98DE",
//               cornerRadius: "md",
//               paddingTop: "8px",
//               paddingBottom: "8px",
//               action: {
//                 type: "uri",
//                 label: "action",
//                 uri: "https://cheni.com.tw/market.php",
//               },
//             },
//             {
//               type: "box",
//               layout: "vertical",
//               contents: [
//                 {
//                   type: "text",
//                   text: "聯繫官方LINE",
//                   color: "#ffffff",
//                   align: "center",
//                 },
//               ],
//               backgroundColor: "#8F98DE",
//               cornerRadius: "md",
//               paddingTop: "8px",
//               paddingBottom: "8px",
//               action: {
//                 type: "uri",
//                 label: "action",
//                 uri: "https://lin.ee/HnB194r",
//               },
//             },
//             {
//               type: "box",
//               layout: "vertical",
//               contents: [
//                 {
//                   type: "text",
//                   text: "分享名片給朋友",
//                   color: "#ffffff",
//                   align: "center",
//                 },
//               ],
//               backgroundColor: "#5E5DB0",
//               cornerRadius: "md",
//               paddingTop: "8px",
//               paddingBottom: "8px",
//               action: {
//                 type: "uri",
//                 label: "分享名片給朋友",
//                 uri: "https://business.cheni.tw/share/4b4553dc-9186-4908-84e0-f5f2a79e40c4",
//               },
//             },
//             {
//               type: "box",
//               layout: "vertical",
//               contents: [],
//               margin: "sm",
//             },
//           ],
//           flex: 0,
//         },
//       },
//       {
//         type: "bubble",
//         hero: {
//           type: "image",
//           url: "https://business.cheni.tw/uploads/images/card_bubbles/1749521812_04.jpg",
//           size: "full",
//           aspectRatio: "20:15",
//           aspectMode: "cover",
//           action: {
//             type: "uri",
//             uri: "https://line.me/",
//           },
//         },
//         body: {
//           type: "box",
//           layout: "vertical",
//           contents: [
//             {
//               type: "text",
//               text: "LINE CARD I 數位名片",
//               size: "xl",
//               weight: "bold",
//               color: "#2B2177",
//             },
//             {
//               type: "box",
//               layout: "vertical",
//               margin: "md",
//               contents: [
//                 {
//                   type: "text",
//                   text: "免印刷 / 隨時調整 / 傳播快速",
//                   size: "sm",
//                   color: "#575770",
//                   margin: "md",
//                   flex: 0,
//                 },
//                 {
//                   type: "text",
//                   text: "服務項目•業務內容•優質商品清晰呈現",
//                   size: "sm",
//                   color: "#575770",
//                   margin: "md",
//                   flex: 0,
//                 },
//                 {
//                   type: "text",
//                   text: "引流客戶速度快 / 串聯多方連結",
//                   size: "sm",
//                   color: "#575770",
//                   margin: "md",
//                   flex: 0,
//                 },
//                 {
//                   type: "text",
//                   text: "展現企業數位化與現代化的專業形象",
//                   size: "sm",
//                   color: "#575770",
//                   margin: "md",
//                   flex: 0,
//                 },
//               ],
//             },
//           ],
//         },
//         footer: {
//           type: "box",
//           layout: "vertical",
//           spacing: "sm",
//           contents: [
//             {
//               type: "box",
//               layout: "vertical",
//               contents: [
//                 {
//                   type: "text",
//                   text: "聯繫官方LINE",
//                   color: "#ffffff",
//                   align: "center",
//                 },
//               ],
//               backgroundColor: "#9BAFF0",
//               cornerRadius: "md",
//               paddingTop: "8px",
//               paddingBottom: "8px",
//               action: {
//                 type: "uri",
//                 label: "action",
//                 uri: "https://lin.ee/HnB194r",
//               },
//             },
//             {
//               type: "box",
//               layout: "vertical",
//               contents: [
//                 {
//                   type: "text",
//                   text: "分享名片給朋友",
//                   color: "#ffffff",
//                   align: "center",
//                 },
//               ],
//               backgroundColor: "#6F88D9",
//               cornerRadius: "md",
//               paddingTop: "8px",
//               paddingBottom: "8px",
//               action: {
//                 type: "uri",
//                 label: "action",
//                 uri: "https://business.cheni.tw/share/4b4553dc-9186-4908-84e0-f5f2a79e40c4",
//               },
//             },
//             {
//               type: "box",
//               layout: "vertical",
//               contents: [],
//               margin: "sm",
//             },
//           ],
//           flex: 0,
//         },
//       },
//       {
//         type: "bubble",
//         hero: {
//           type: "image",
//           url: "https://business.cheni.tw/uploads/images/card_bubbles/1749522238_05.jpg",
//           size: "full",
//           aspectRatio: "20:15",
//           aspectMode: "cover",
//           action: {
//             type: "uri",
//             uri: "https://line.me/",
//           },
//         },
//         body: {
//           type: "box",
//           layout: "vertical",
//           contents: [
//             {
//               type: "text",
//               text: "FIND US I 誠翊資訊",
//               size: "xl",
//               weight: "bold",
//               color: "#2B2177",
//             },
//             {
//               type: "box",
//               layout: "vertical",
//               margin: "md",
//               contents: [
//                 {
//                   type: "text",
//                   text: "誠翊資訊在網路領域中給您最專業的服務",
//                   size: "sm",
//                   color: "#575770",
//                   margin: "md",
//                   flex: 0,
//                 },
//                 {
//                   type: "text",
//                   text: "打造品牌數位力  提升您的品牌價值",
//                   size: "sm",
//                   color: "#575770",
//                   margin: "md",
//                   flex: 0,
//                 },
//                 {
//                   type: "text",
//                   text: "專職 I 專注 I 專業 I 專精",
//                   size: "sm",
//                   color: "#575770",
//                   margin: "md",
//                   flex: 0,
//                 },
//                 {
//                   type: "text",
//                   text: "我們有著無比熱忱  歡迎您隨時預約洽談",
//                   size: "sm",
//                   color: "#575770",
//                   margin: "md",
//                   flex: 0,
//                 },
//               ],
//             },
//           ],
//         },
//         footer: {
//           type: "box",
//           layout: "vertical",
//           spacing: "sm",
//           contents: [
//             {
//               type: "box",
//               layout: "vertical",
//               contents: [
//                 {
//                   type: "text",
//                   text: "公司位置",
//                   color: "#ffffff",
//                   align: "center",
//                 },
//               ],
//               backgroundColor: "#8DB4EE",
//               cornerRadius: "md",
//               paddingTop: "8px",
//               paddingBottom: "8px",
//               action: {
//                 type: "uri",
//                 label: "action",
//                 uri: "https://www.google.com/maps/place/%E8%AA%A0%E7%BF%8A%E8%B3%87%E8%A8%8A%E7%B6%B2%E8%B7%AF%E6%87%89%E7%94%A8%E4%BA%8B%E6%A5%AD/@23.9783505,121.5864335,17z/data=!3m1!4b1!4m6!3m5!1s0x34689f98cfc4101f:0x3f233299ee6f2005!8m2!3d23.9783456!4d121.5890084!16s%2Fg%2F1hhgp5gnn?entry=ttu&g_ep=EgoyMDI0MTIxMS4wIKXMDSoASAFQAw%3D%3D",
//               },
//             },
//             {
//               type: "box",
//               layout: "vertical",
//               contents: [
//                 {
//                   type: "text",
//                   text: "FACEBOOK",
//                   color: "#ffffff",
//                   align: "center",
//                 },
//               ],
//               backgroundColor: "#5F93DF",
//               cornerRadius: "md",
//               paddingTop: "8px",
//               paddingBottom: "8px",
//               action: {
//                 type: "uri",
//                 label: "action",
//                 uri: "https://www.facebook.com/chenitw",
//               },
//             },
//             {
//               type: "box",
//               layout: "vertical",
//               contents: [],
//               margin: "sm",
//             },
//           ],
//           flex: 0,
//         },
//       },
//       {
//         type: "bubble",
//         hero: {
//           type: "image",
//           url: "https://developers-resource.landpress.line.me/fx/img/01_1_cafe.png",
//           size: "full",
//           aspectRatio: "20:13",
//           aspectMode: "cover",
//           action: {
//             type: "uri",
//             uri: "https://line.me/",
//           },
//         },
//         body: {
//           type: "box",
//           layout: "vertical",
//           contents: [
//             {
//               type: "text",
//               text: "Brown Cafe",
//               weight: "bold",
//               size: "xl",
//             },
//             {
//               type: "box",
//               layout: "baseline",
//               margin: "md",
//               contents: [
//                 {
//                   type: "icon",
//                   size: "sm",
//                   url: "https://developers-resource.landpress.line.me/fx/img/review_gold_star_28.png",
//                 },
//                 {
//                   type: "icon",
//                   size: "sm",
//                   url: "https://developers-resource.landpress.line.me/fx/img/review_gold_star_28.png",
//                 },
//                 {
//                   type: "icon",
//                   size: "sm",
//                   url: "https://developers-resource.landpress.line.me/fx/img/review_gold_star_28.png",
//                 },
//                 {
//                   type: "icon",
//                   size: "sm",
//                   url: "https://developers-resource.landpress.line.me/fx/img/review_gold_star_28.png",
//                 },
//                 {
//                   type: "icon",
//                   size: "sm",
//                   url: "https://developers-resource.landpress.line.me/fx/img/review_gray_star_28.png",
//                 },
//                 {
//                   type: "text",
//                   text: "4.0",
//                   size: "sm",
//                   color: "#999999",
//                   margin: "md",
//                   flex: 0,
//                 },
//               ],
//             },
//             {
//               type: "box",
//               layout: "vertical",
//               margin: "lg",
//               spacing: "sm",
//               contents: [
//                 {
//                   type: "box",
//                   layout: "baseline",
//                   spacing: "sm",
//                   contents: [
//                     {
//                       type: "text",
//                       text: "Place",
//                       color: "#aaaaaa",
//                       size: "sm",
//                       flex: 1,
//                     },
//                     {
//                       type: "text",
//                       text: "Flex Tower, 7-7-4 Midori-ku, Tokyo",
//                       wrap: true,
//                       color: "#666666",
//                       size: "sm",
//                       flex: 5,
//                     },
//                   ],
//                 },
//                 {
//                   type: "box",
//                   layout: "baseline",
//                   spacing: "sm",
//                   contents: [
//                     {
//                       type: "text",
//                       text: "Time",
//                       color: "#aaaaaa",
//                       size: "sm",
//                       flex: 1,
//                     },
//                     {
//                       type: "text",
//                       text: "10:00 - 23:00",
//                       wrap: true,
//                       color: "#666666",
//                       size: "sm",
//                       flex: 5,
//                     },
//                   ],
//                 },
//               ],
//             },
//           ],
//         },
//         footer: {
//           type: "box",
//           layout: "vertical",
//           spacing: "sm",
//           contents: [
//             {
//               type: "button",
//               style: "link",
//               height: "sm",
//               action: {
//                 type: "uri",
//                 label: "CALL",
//                 uri: "https://line.me/",
//               },
//             },
//             {
//               type: "button",
//               style: "link",
//               height: "sm",
//               action: {
//                 type: "uri",
//                 label: "WEBSITE",
//                 uri: "https://line.me/",
//               },
//             },
//             {
//               type: "box",
//               layout: "vertical",
//               contents: [],
//               margin: "sm",
//             },
//           ],
//           flex: 0,
//         },
//       },
//       {
//         type: "bubble",
//         body: {
//           type: "box",
//           layout: "vertical",
//           contents: [
//             {
//               type: "image",
//               url: "https://developers-resource.landpress.line.me/fx/clip/clip1.jpg",
//               size: "full",
//               aspectMode: "cover",
//               aspectRatio: "2:3",
//               gravity: "top",
//             },
//             {
//               type: "box",
//               layout: "vertical",
//               contents: [
//                 {
//                   type: "box",
//                   layout: "vertical",
//                   contents: [
//                     {
//                       type: "text",
//                       text: "Brown's T-shirts",
//                       size: "xl",
//                       color: "#ffffff",
//                       weight: "bold",
//                     },
//                   ],
//                 },
//                 {
//                   type: "box",
//                   layout: "baseline",
//                   contents: [
//                     {
//                       type: "text",
//                       text: "¥35,800",
//                       color: "#ebebeb",
//                       size: "sm",
//                       flex: 0,
//                     },
//                     {
//                       type: "text",
//                       text: "¥75,000",
//                       color: "#ffffffcc",
//                       decoration: "line-through",
//                       gravity: "bottom",
//                       flex: 0,
//                       size: "sm",
//                     },
//                   ],
//                   spacing: "lg",
//                 },
//                 {
//                   type: "box",
//                   layout: "vertical",
//                   contents: [
//                     {
//                       type: "filler",
//                     },
//                     {
//                       type: "box",
//                       layout: "baseline",
//                       contents: [
//                         {
//                           type: "filler",
//                         },
//                         {
//                           type: "icon",
//                           url: "https://developers-resource.landpress.line.me/fx/clip/clip14.png",
//                         },
//                         {
//                           type: "text",
//                           text: "Add to cart",
//                           color: "#ffffff",
//                           flex: 0,
//                           offsetTop: "-2px",
//                         },
//                         {
//                           type: "filler",
//                         },
//                       ],
//                       spacing: "sm",
//                     },
//                     {
//                       type: "filler",
//                     },
//                   ],
//                   borderWidth: "1px",
//                   cornerRadius: "4px",
//                   spacing: "sm",
//                   borderColor: "#ffffff",
//                   margin: "xxl",
//                   height: "40px", // 任意 “40px”
//                 },
//               ],
//               position: "absolute",
//               offsetBottom: "0px",
//               offsetStart: "0px",
//               offsetEnd: "0px",
//               backgroundColor: "#03303Acc",
//               paddingAll: "20px",
//               paddingTop: "18px",
//             },
//             {
//               type: "box",
//               layout: "vertical",
//               contents: [
//                 {
//                   type: "text",
//                   text: "SALE",
//                   color: "#ffffff",
//                   align: "center",
//                   size: "xs",
//                   offsetTop: "3px", // 现在会用 position:relative + top:3px
//                 },
//               ],
//               position: "absolute",
//               cornerRadius: "20px",
//               offsetTop: "18px",
//               backgroundColor: "#ff334b",
//               offsetStart: "18px",
//               height: "25px", // 任意 “25px”
//               width: "53px", // 任意 “53px”
//             },
//           ],
//           paddingAll: "0px",
//         },
//       },
//       {
//         type: "bubble",
//         body: {
//           type: "box",
//           layout: "vertical",
//           contents: [
//             {
//               type: "image",
//               url: "https://developers-resource.landpress.line.me/fx/clip/clip2.jpg",
//               size: "full",
//               aspectMode: "cover",
//               aspectRatio: "2:3",
//               gravity: "top",
//             },
//             {
//               type: "box",
//               layout: "vertical",
//               contents: [
//                 {
//                   type: "box",
//                   layout: "vertical",
//                   contents: [
//                     {
//                       type: "text",
//                       text: "Cony's T-shirts",
//                       size: "xl",
//                       color: "#ffffff",
//                       weight: "bold",
//                     },
//                   ],
//                 },
//                 {
//                   type: "box",
//                   layout: "baseline",
//                   contents: [
//                     {
//                       type: "text",
//                       text: "¥35,800",
//                       color: "#ebebeb",
//                       size: "sm",
//                       flex: 0,
//                     },
//                     {
//                       type: "text",
//                       text: "¥75,000",
//                       color: "#ffffffcc",
//                       decoration: "line-through",
//                       gravity: "bottom",
//                       flex: 0,
//                       size: "sm",
//                     },
//                   ],
//                   spacing: "lg",
//                 },
//                 {
//                   type: "box",
//                   layout: "vertical",
//                   contents: [
//                     {
//                       type: "filler",
//                     },
//                     {
//                       type: "box",
//                       layout: "baseline",
//                       contents: [
//                         {
//                           type: "filler",
//                         },
//                         {
//                           type: "icon",
//                           url: "https://developers-resource.landpress.line.me/fx/clip/clip14.png",
//                         },
//                         {
//                           type: "text",
//                           text: "Add to cart",
//                           color: "#ffffff",
//                           flex: 0,
//                           offsetTop: "-2px",
//                         },
//                         {
//                           type: "filler",
//                         },
//                       ],
//                       spacing: "sm",
//                     },
//                     {
//                       type: "filler",
//                     },
//                   ],
//                   borderWidth: "1px",
//                   cornerRadius: "4px",
//                   spacing: "sm",
//                   borderColor: "#ffffff",
//                   margin: "xxl",
//                   height: "40px", // 任意 “40px”
//                 },
//               ],
//               position: "absolute",
//               offsetBottom: "0px",
//               offsetStart: "0px",
//               offsetEnd: "0px",
//               backgroundColor: "#9C8E7Ecc",
//               paddingAll: "20px",
//               paddingTop: "18px",
//             },
//             {
//               type: "box",
//               layout: "vertical",
//               contents: [
//                 {
//                   type: "text",
//                   text: "SALE",
//                   color: "#ffffff",
//                   align: "center",
//                   size: "xs",
//                   offsetTop: "3px", // 同样会用 position:relative + top:3px
//                 },
//               ],
//               position: "absolute",
//               cornerRadius: "20px",
//               offsetTop: "18px",
//               backgroundColor: "#ff334b",
//               offsetStart: "18px",
//               height: "25px", // 任意 “25px”
//               width: "53px", // 任意 “53px”
//             },
//           ],
//           paddingAll: "0px",
//         },
//       }

//     ],
//   };

//   const root = document.getElementById("flex-root");
//   if (root && flexJson) {
//     const rendered = renderFlexComponent(flexJson);
//     if (rendered) {
//       root.appendChild(rendered);
//     } else {
//       root.textContent = "Error rendering Flex Message or empty JSON.";
//     }
//   } else {
//     console.error("#flex-root element not found or flexJson is null.");
//   }
});
