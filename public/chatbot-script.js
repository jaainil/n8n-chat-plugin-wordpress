document.addEventListener("DOMContentLoaded", () => {
  const chatContainer = document.querySelector(".oacb-chat-container");
  const chatIcon = document.createElement("img");
  chatIcon.className = "oacb-chat-icon";
  chatIcon.src = oacbConfig.chatIcon;

  // Add chat icon to container
  chatContainer.appendChild(chatIcon);

  const chatWindow = document.getElementById("oacb-chat-window");
  const messagesContainer = document.getElementById("oacb-messages");
  const messageInput = document.getElementById("oacb-message-input");
  const sendButton = document.getElementById("oacb-send-button");
  const welcomePopup = document.getElementById("oacb-welcome-popup");
  const popupCloseBtn = document.querySelector(".oacb-popup-close");
  const popupStartChatBtn = document.querySelector(".oacb-popup-start-chat");

  let isOpen = false;
  let popupShown = false;

  // Set position
  chatContainer.className = `oacb-chat-container ${oacbConfig.position}`;

  // Set popup size
  welcomePopup.className = `oacb-welcome-popup size-${oacbConfig.popupSize}`;

  // Position welcome popup based on chat position
  function positionWelcomePopup() {
    if (oacbConfig.position.includes("left")) {
      welcomePopup.style.right = "auto";
      welcomePopup.style.left = "20px";
    } else {
      welcomePopup.style.left = "auto";
      welcomePopup.style.right = "20px";
    }

    if (oacbConfig.position.includes("top")) {
      welcomePopup.style.bottom = "auto";
      welcomePopup.style.top = "100px";
    } else {
      welcomePopup.style.top = "auto";
      welcomePopup.style.bottom = "100px";
    }
  }

  positionWelcomePopup();

  // Show welcome popup after delay
  function showWelcomePopup() {
    if (oacbConfig.enablePopup === "1" && !popupShown && !isOpen) {
      setTimeout(() => {
        welcomePopup.style.display = "block";
        popupShown = true;
      }, parseInt(oacbConfig.popupDelay) * 1000);
    }
  }

  // Start showing popup after page load
  showWelcomePopup();

  // Toggle chat window
  chatIcon.addEventListener("click", () => {
    isOpen = !isOpen;
    chatWindow.style.display = isOpen ? "block" : "none";
    welcomePopup.style.display = "none";

    if (isOpen) {
      messageInput.focus();
    }
  });

  // Close popup
  popupCloseBtn.addEventListener("click", () => {
    welcomePopup.style.display = "none";
  });

  // Start chat from popup
  popupStartChatBtn.addEventListener("click", () => {
    welcomePopup.style.display = "none";
    isOpen = true;
    chatWindow.style.display = "block";
    messageInput.focus();
  });

  // Handle message sending
  function sendMessage() {
    const message = messageInput.value.trim();
    if (!message) return;

    // Add user message
    appendMessage(message, "user");
    messageInput.value = "";

    // Send to backend
    fetch(oacbConfig.apiUrl, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-WP-Nonce": oacbConfig.nonce,
      },
      body: JSON.stringify({ message }),
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          appendMessage(data.response, "bot");
        } else {
          showError("Failed to get response");
        }
      })
      .catch(() => showError("Connection error"));
  }

  // Append message to chat
  function appendMessage(text, sender) {
    const messageDiv = document.createElement("div");
    messageDiv.className = `oacb-message oacb-${sender}-message`;

    const content = document.createElement("div");
    content.className = "oacb-message-content";
    content.textContent = text;

    messageDiv.appendChild(content);
    messagesContainer.appendChild(messageDiv);
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
  }

  // Error handling
  function showError(message) {
    const errorDiv = document.createElement("div");
    errorDiv.className = "oacb-error";
    errorDiv.textContent = message;
    messagesContainer.appendChild(errorDiv);
  }

  // Event listeners
  sendButton.addEventListener("click", sendMessage);
  messageInput.addEventListener("keypress", (e) => {
    if (e.key === "Enter" && !e.shiftKey) {
      e.preventDefault();
      sendMessage();
    }
  });

  // Close chat when clicking outside
  document.addEventListener("click", (e) => {
    if (
      isOpen &&
      !chatWindow.contains(e.target) &&
      !chatIcon.contains(e.target) &&
      !welcomePopup.contains(e.target)
    ) {
      isOpen = false;
      chatWindow.style.display = "none";
    }
  });
});
